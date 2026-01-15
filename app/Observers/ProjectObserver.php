<?php

namespace App\Observers;

use App\Models\Project;
use App\Models\ProjectMarketerHistory;

class ProjectObserver
{
    public function created(Project $project): void
    {
        if ($project->marketer_id) {
            ProjectMarketerHistory::create([
                'project_id' => $project->id,
                'user_id' => $project->marketer_id,
                'assigned_at' => now(),
                'assigned_by' => auth()->id(),
            ]);
        }
    }

    public function updating(Project $project): void
    {
        // Если маркетолог изменился — записываем в историю
        if ($project->isDirty('marketer_id')) {
            $oldMarketerId = $project->getOriginal('marketer_id');
            $newMarketerId = $project->marketer_id;

            // Закрываем старое назначение
            if ($oldMarketerId) {
                ProjectMarketerHistory::where('project_id', $project->id)
                    ->where('user_id', $oldMarketerId)
                    ->whereNull('unassigned_at')
                    ->update(['unassigned_at' => now()]);
            }

            // Открываем новое назначение
            if ($newMarketerId) {
                ProjectMarketerHistory::create([
                    'project_id' => $project->id,
                    'user_id' => $newMarketerId,
                    'assigned_at' => now(),
                    'assigned_by' => auth()->id(),
                ]);
            }
        }

        // Если проект закрывается — закрываем активное назначение маркетолога
        if ($project->isDirty('closed_at')) {
            $oldClosedAt = $project->getOriginal('closed_at');
            $newClosedAt = $project->closed_at;

            if ($newClosedAt && ! $oldClosedAt) {
                // Проект закрывается — закрываем активное назначение
                ProjectMarketerHistory::where('project_id', $project->id)
                    ->whereNull('unassigned_at')
                    ->update([
                        'unassigned_at' => $newClosedAt,
                        'reason' => 'project_closed',
                    ]);
            } elseif (! $newClosedAt && $oldClosedAt) {
                // Проект открывается обратно — открываем назначение, закрытое из-за закрытия проекта
                ProjectMarketerHistory::where('project_id', $project->id)
                    ->where('reason', 'project_closed')
                    ->update([
                        'unassigned_at' => null,
                        'reason' => null,
                    ]);
            } elseif ($newClosedAt && $oldClosedAt && ! $newClosedAt->eq($oldClosedAt)) {
                // Дата закрытия изменилась — обновляем unassigned_at
                ProjectMarketerHistory::where('project_id', $project->id)
                    ->where('reason', 'project_closed')
                    ->update([
                        'unassigned_at' => $newClosedAt,
                    ]);
            }
        }
    }
}
