<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TaskComment;
use App\Models\TaskDeadlineChange;
use App\Models\TaskDispute;
use App\Models\TaskStatus;
use App\Models\User;
use App\Notifications\TaskDisputeOpened;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class TaskService
{
    public function create(array $data, array $coExecutorIds = [], array $observerIds = [], ?int $actorId = null): Task
    {
        return DB::transaction(function () use ($data, $coExecutorIds, $observerIds, $actorId) {
            $task = Task::create($data);

            $this->syncParticipants($task, $coExecutorIds, $observerIds, $data['assignee_id'] ?? null, $actorId ?? $data['created_by'] ?? null);

            TaskComment::create([
                'task_id' => $task->id,
                'user_id' => $data['created_by'] ?? null,
                'type' => 'system',
                'message' => 'Задача создана',
                'meta' => [
                    'title' => $task->title,
                ],
            ]);

            return $task;
        });
    }

    public function update(Task $task, array $data, array $coExecutorIds = [], array $observerIds = [], ?int $actorId = null): Task
    {
        return DB::transaction(function () use ($task, $data, $coExecutorIds, $observerIds, $actorId) {
            $task->fill($data);
            $task->save();

            $this->syncParticipants($task, $coExecutorIds, $observerIds, $data['assignee_id'] ?? $task->assignee_id, $actorId ?? $task->created_by);

            return $task;
        });
    }

    public function changeStatus(Task $task, TaskStatus $status, User $user): Task
    {
        return DB::transaction(function () use ($task, $status, $user) {
            $oldStatusId = $task->status_id;
            $task->status_id = $status->id;

            if ($status->slug === 'done') {
                $task->closed_at = now();
            } else {
                $task->closed_at = null;
            }

            $task->save();

            TaskComment::create([
                'task_id' => $task->id,
                'user_id' => $user->id,
                'type' => 'status_change',
                'message' => null,
                'meta' => [
                    'from_status_id' => $oldStatusId,
                    'to_status_id' => $status->id,
                ],
            ]);

            return $task;
        });
    }

    public function changeDeadline(Task $task, ?string $newDeadlineAt, User $user, ?string $reason = null): Task
    {
        return DB::transaction(function () use ($task, $newDeadlineAt, $user, $reason) {
            $oldDeadline = $task->deadline_at;
            $task->deadline_at = $newDeadlineAt;
            $task->save();

            TaskDeadlineChange::create([
                'task_id' => $task->id,
                'changed_by' => $user->id,
                'old_deadline_at' => $oldDeadline,
                'new_deadline_at' => $newDeadlineAt,
                'reason' => $reason,
            ]);

            TaskComment::create([
                'task_id' => $task->id,
                'user_id' => $user->id,
                'type' => 'deadline_change',
                'message' => $reason,
                'meta' => [
                    'old_deadline_at' => $oldDeadline,
                    'new_deadline_at' => $newDeadlineAt,
                ],
            ]);

            return $task;
        });
    }

    public function addComment(Task $task, User $user, string $message): TaskComment
    {
        return TaskComment::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'type' => 'comment',
            'message' => $message,
        ]);
    }

    public function close(Task $task, User $user): Task
    {
        return DB::transaction(function () use ($task, $user) {
            $statusDone = TaskStatus::where('slug', 'done')->first();
            if ($statusDone) {
                $task->status_id = $statusDone->id;
            }
            $task->closed_at = now();
            $task->save();

            TaskComment::create([
                'task_id' => $task->id,
                'user_id' => $user->id,
                'type' => 'status_change',
                'message' => 'Задача закрыта',
                'meta' => [
                    'closed_at' => $task->closed_at,
                ],
            ]);

            return $task;
        });
    }

    public function openDispute(Task $task, User $user, ?string $reason = null): TaskDispute
    {
        return DB::transaction(function () use ($task, $user, $reason) {
            $dispute = TaskDispute::create([
                'task_id' => $task->id,
                'opened_by' => $user->id,
                'status' => TaskDispute::STATUS_OPEN,
                'reason' => $reason,
            ]);

            $task->status_locked = true;
            $task->status_locked_at = now();
            $task->save();

            TaskComment::create([
                'task_id' => $task->id,
                'user_id' => $user->id,
                'type' => 'system',
                'message' => 'Открыт спор по закрытию задачи',
                'meta' => [
                    'dispute_id' => $dispute->id,
                    'reason' => $reason,
                ],
            ]);

            $notifyUsers = User::query()
                ->whereIn('role', [User::ROLE_ADMIN, User::ROLE_PROJECT_MANAGER])
                ->get();

            if ($notifyUsers->isNotEmpty()) {
                Notification::send($notifyUsers, new TaskDisputeOpened($task, $dispute));
            }

            return $dispute;
        });
    }

    public function resolveDispute(TaskDispute $dispute, User $user, string $resolution): TaskDispute
    {
        return DB::transaction(function () use ($dispute, $user, $resolution) {
            $dispute->status = $resolution;
            $dispute->resolved_by = $user->id;
            $dispute->resolved_at = now();
            $dispute->save();

            $task = $dispute->task;
            $task->status_locked = false;
            $task->status_locked_at = null;

            if ($resolution === TaskDispute::STATUS_APPROVED_CLOSE) {
                $statusDone = TaskStatus::where('slug', 'done')->first();
                if ($statusDone) {
                    $task->status_id = $statusDone->id;
                }
                $task->closed_at = now();
            }

            $task->save();

            TaskComment::create([
                'task_id' => $task->id,
                'user_id' => $user->id,
                'type' => 'system',
                'message' => $resolution === TaskDispute::STATUS_APPROVED_CLOSE ? 'Спор решён: закрытие подтверждено' : 'Спор решён: закрытие отклонено',
                'meta' => [
                    'dispute_id' => $dispute->id,
                    'resolution' => $resolution,
                ],
            ]);

            return $dispute;
        });
    }

    public function syncParticipants(Task $task, array $coExecutorIds = [], array $observerIds = [], ?int $assigneeId = null, ?int $actorId = null): void
    {
        $coExecutorIds = array_unique(array_filter($coExecutorIds));
        $observerIds = array_unique(array_filter($observerIds));

        if ($assigneeId) {
            $coExecutorIds = array_values(array_diff($coExecutorIds, [$assigneeId]));
            $observerIds = array_values(array_diff($observerIds, [$assigneeId]));
        }

        $syncData = [];

        if ($assigneeId) {
            $syncData[$assigneeId] = ['role' => 'assignee'];
        }

        foreach ($coExecutorIds as $id) {
            $syncData[$id] = ['role' => 'co_executor'];
        }

        foreach ($observerIds as $id) {
            $syncData[$id] = ['role' => 'observer'];
        }

        $task->users()->sync($syncData);

        TaskComment::create([
            'task_id' => $task->id,
            'user_id' => $actorId,
            'type' => 'participant_change',
            'message' => null,
            'meta' => [
                'assignee_id' => $assigneeId,
                'co_executor_ids' => $coExecutorIds,
                'observer_ids' => $observerIds,
            ],
        ]);
    }
}
