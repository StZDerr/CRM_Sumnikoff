<?php

namespace App\Console\Commands;

use App\Services\RecurringTaskService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateRecurringTasks extends Command
{
    protected $signature = 'tasks:generate-recurring {--date=}';

    protected $description = 'Generate tasks from recurring templates';

    public function handle(RecurringTaskService $service): int
    {
        $date = $this->option('date');
        $created = $service->generateDueTasks($date ? Carbon::parse($date) : null);

        $this->info("Created {$created} tasks.");

        return self::SUCCESS;
    }
}
