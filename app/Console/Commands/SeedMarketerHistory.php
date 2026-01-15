<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\ProjectMarketerHistory;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SeedMarketerHistory extends Command
{
    protected $signature = 'marketers:seed-history {--date=2026-01-01 : Дата начала назначения}';

    protected $description = 'Создать начальную историю маркетологов для существующих проектов';

    public function handle(): int
    {
        $startDate = Carbon::parse($this->option('date'));

        $this->info("Создание истории маркетологов с датой начала: {$startDate->format('d.m.Y')}");

        $projects = Project::whereNotNull('marketer_id')
            ->whereDoesntHave('marketerHistory')
            ->get();

        if ($projects->isEmpty()) {
            $this->info('Нет проектов без истории маркетологов.');
            return self::SUCCESS;
        }

        $this->info("Найдено проектов без истории: {$projects->count()}");

        $bar = $this->output->createProgressBar($projects->count());
        $bar->start();

        $created = 0;

        foreach ($projects as $project) {
            // Определяем дату окончания назначения
            $unassignedAt = null;
            $reason = null;

            // Если проект закрыт — назначение тоже закрыто
            if ($project->closed_at) {
                $unassignedAt = $project->closed_at;
                $reason = 'project_closed';
            }

            ProjectMarketerHistory::create([
                'project_id' => $project->id,
                'user_id' => $project->marketer_id,
                'assigned_at' => $startDate,
                'unassigned_at' => $unassignedAt,
                'reason' => $reason,
                'assigned_by' => null, // Системное создание
            ]);

            $created++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info("Создано записей истории: {$created}");

        return self::SUCCESS;
    }
}
