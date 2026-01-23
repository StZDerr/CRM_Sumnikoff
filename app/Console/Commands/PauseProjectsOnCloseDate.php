<?php

namespace App\Console\Commands;

use App\Models\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PauseProjectsOnCloseDate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'projects:pause-on-close-date';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pause projects whose close date is today';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = now()->toDateString();

        $query = Project::query()
            ->whereDate('closed_at', $today)
            ->where('status', '!=', Project::STATUS_PAUSED);

        $projects = $query->get(['id', 'title', 'status']);

        // Debug logging: which projects will be paused
        if ($projects->isEmpty()) {
            Log::info("PauseProjectsOnCloseDate: found 0 projects for {$today}");
        } else {
            Log::info("PauseProjectsOnCloseDate: will pause projects for {$today}: ".$projects->pluck('id')->join(',').' - titles: '.$projects->pluck('title')->map(fn ($t) => substr($t, 0, 50))->join(' | '));
        }

        $updated = $query->update(['status' => Project::STATUS_PAUSED]);

        Log::info("PauseProjectsOnCloseDate: updated count = {$updated}");

        $this->info("Paused projects: {$updated}");

        return self::SUCCESS;
    }
}
