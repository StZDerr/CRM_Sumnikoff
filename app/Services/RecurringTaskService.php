<?php

namespace App\Services;

use App\Models\RecurringTask;
use App\Models\Task;
use App\Models\TaskComment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RecurringTaskService
{
    public function generateDueTasks(?Carbon $date = null): int
    {
        $date = ($date ?: now())->copy()->startOfDay();
        $createdCount = 0;

        $recurringTasks = RecurringTask::with(['rules'])
            ->active()
            ->get();

        foreach ($recurringTasks as $recurringTask) {
            if ($recurringTask->starts_at && $date->lt($recurringTask->starts_at)) {
                continue;
            }

            if ($recurringTask->ends_at && $date->gt($recurringTask->ends_at)) {
                continue;
            }

            foreach ($recurringTask->rules as $rule) {
                if (! $this->isRuleDueOnDate($rule, $date, $recurringTask)) {
                    continue;
                }

                $deadlineAt = $this->buildDeadlineAt($date, $rule->time_of_day);

                $created = $this->createTaskIfNotExists($recurringTask, $date, $deadlineAt, $rule->id);
                if ($created) {
                    $createdCount++;
                }
            }
        }

        return $createdCount;
    }

    protected function createTaskIfNotExists(RecurringTask $recurringTask, Carbon $date, ?Carbon $deadlineAt, ?int $ruleId): bool
    {
        return DB::transaction(function () use ($recurringTask, $date, $deadlineAt, $ruleId) {
            $existing = Task::where('recurring_task_id', $recurringTask->id)
                ->whereDate('recurring_occurrence_date', $date->toDateString())
                ->first();

            if ($existing) {
                return false;
            }

            $task = Task::create([
                'project_id' => $recurringTask->project_id,
                'status_id' => $recurringTask->status_id,
                'created_by' => $recurringTask->created_by,
                'assignee_id' => $recurringTask->assignee_id,
                'recurring_task_id' => $recurringTask->id,
                'title' => $recurringTask->title,
                'description' => $recurringTask->description,
                'deadline_at' => $deadlineAt,
                'recurring_occurrence_date' => $date->toDateString(),
            ]);

            $task->users()->sync([
                $recurringTask->assignee_id => ['role' => 'assignee'],
            ]);

            TaskComment::create([
                'task_id' => $task->id,
                'user_id' => $recurringTask->created_by,
                'type' => 'system',
                'message' => 'Создана задача из регулярного шаблона',
                'meta' => [
                    'recurring_task_id' => $recurringTask->id,
                    'rule_id' => $ruleId,
                ],
            ]);

            return true;
        });
    }

    protected function isRuleDueOnDate($rule, Carbon $date, RecurringTask $recurringTask): bool
    {
        $startDate = $rule->start_date ?: $recurringTask->starts_at;
        if ($startDate && $date->lt(Carbon::parse($startDate)->startOfDay())) {
            return false;
        }

        if ($rule->type === 'daily') {
            $interval = $rule->interval_days ?: 1;
            $start = Carbon::parse($startDate ?: $date->copy()->startOfDay());
            $diff = $start->diffInDays($date);

            return $diff % $interval === 0;
        }

        if ($rule->type === 'weekly') {
            $weeklyDays = $rule->weekly_days ?: [];
            $weekday = (int) $date->dayOfWeekIso;

            return in_array($weekday, $weeklyDays, true);
        }

        if ($rule->type === 'monthly') {
            $monthlyRules = $rule->monthly_rules ?: [];
            $weekday = (int) $date->dayOfWeekIso;
            $weekOfMonth = (int) $date->weekOfMonth;

            foreach ($monthlyRules as $item) {
                if ((int) ($item['weekday'] ?? 0) === $weekday && (int) ($item['week'] ?? 0) === $weekOfMonth) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function buildDeadlineAt(Carbon $date, ?string $timeOfDay): ?Carbon
    {
        if (! $timeOfDay) {
            return $date->copy()->endOfDay();
        }

        return Carbon::parse($date->toDateString().' '.$timeOfDay);
    }
}
