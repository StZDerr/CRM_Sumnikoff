<?php

namespace App\Console\Commands;

use App\Models\WorkDay;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoCloseWorkDays extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'worktime:auto-close';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically close open work days for past dates (runs at 00:00)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = now()->toDateString();

        // close any work_day that is not closed and whose work_date is before today
        $query = WorkDay::query()
            ->where('is_closed', false)
            ->whereDate('work_date', '<', $today);

        $days = $query->with(['sessions' => fn ($q) => $q->whereNull('ended_at'), 'breaks' => fn ($q) => $q->whereNull('ended_at')])->get();

        if ($days->isEmpty()) {
            Log::info("AutoCloseWorkDays: nothing to close for {$today}");
            $this->info('No open work days to close.');
            return self::SUCCESS;
        }

        Log::info("AutoCloseWorkDays: closing {$days->count()} work_days for dates before {$today}");

        foreach ($days as $day) {
            $endAt = Carbon::parse($day->work_date)->endOfDay();

            // close open breaks
            foreach ($day->breaks as $break) {
                if (! $break->started_at) {
                    continue;
                }

                $effectiveEnd = $endAt->copy();
                if ($effectiveEnd->lt($break->started_at)) {
                    $effectiveEnd = $break->started_at->copy();
                }

                $break->update([
                    'ended_at' => $effectiveEnd,
                    'minutes' => $break->started_at->diffInMinutes($effectiveEnd),
                ]);
            }

            // close open sessions
            foreach ($day->sessions as $session) {
                if (! $session->started_at) {
                    continue;
                }

                $effectiveEnd = $endAt->copy();
                if ($effectiveEnd->lt($session->started_at)) {
                    $effectiveEnd = $session->started_at->copy();
                }

                $session->update([
                    'ended_at' => $effectiveEnd,
                    'minutes' => $session->started_at->diffInMinutes($effectiveEnd),
                ]);
            }

            // recalculate totals
            $workMinutes = (int) $day->sessions()->whereNotNull('ended_at')->sum('minutes');
            $breakMinutes = (int) $day->breaks()->whereNotNull('ended_at')->sum('minutes');

            $day->update([
                'total_work_minutes' => $workMinutes,
                'total_break_minutes' => $breakMinutes,
                'is_closed' => true,
            ]);

            Log::info(sprintf('AutoCloseWorkDays: closed work_day id=%s user_id=%s date=%s (work=%d, break=%d)', $day->id, $day->user_id, $day->work_date, $workMinutes, $breakMinutes));
        }

        $this->info('Auto-closed '.count($days).' work days.');

        return self::SUCCESS;
    }
}
