<?php

namespace App\Console\Commands;

use App\Models\Project;
use Illuminate\Console\Command;

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

        $updated = Project::query()
            ->whereDate('closed_at', $today)
            ->where('status', '!=', Project::STATUS_PAUSED)
            ->update(['status' => Project::STATUS_PAUSED]);

        $this->info("Paused projects: {$updated}");

        return self::SUCCESS;
    }
}
