<?php

namespace App\Services;

use App\Models\RecurringTask;
use App\Models\Task;
use App\Models\User;

class TaskPermissionService
{
    public function isParticipant(User $user, Task $task): bool
    {
        if ($task->created_by === $user->id || $task->assignee_id === $user->id) {
            return true;
        }

        return $task->users()->where('users.id', $user->id)->exists();
    }

    public function canView(User $user, Task $task): bool
    {
        if ($user->isAdmin() || $user->isProjectManager()) {
            return true;
        }

        return $this->isParticipant($user, $task);
    }

    public function canEdit(User $user, Task $task): bool
    {
        return $this->canView($user, $task);
    }

    public function canChangeStatus(User $user, Task $task): bool
    {
        if ($task->status_locked) {
            return $user->isAdmin() || $user->isProjectManager();
        }

        return $this->isParticipant($user, $task);
    }

    public function canChangeDeadline(User $user, Task $task): bool
    {
        if ($task->status_locked) {
            return $user->isAdmin() || $user->isProjectManager();
        }

        return $this->isParticipant($user, $task);
    }

    public function canClose(User $user, Task $task): bool
    {
        if ($task->status_locked) {
            return $user->isAdmin() || $user->isProjectManager();
        }

        return $this->isParticipant($user, $task);
    }

    public function canComment(User $user, Task $task): bool
    {
        return $this->canView($user, $task);
    }

    public function canViewRecurring(User $user, RecurringTask $recurringTask): bool
    {
        if ($user->isAdmin() || $user->isProjectManager()) {
            return true;
        }

        return $recurringTask->created_by === $user->id || $recurringTask->assignee_id === $user->id;
    }

    public function canManageRecurring(User $user, RecurringTask $recurringTask): bool
    {
        if ($user->isAdmin() || $user->isProjectManager()) {
            return true;
        }

        return $recurringTask->created_by === $user->id || $recurringTask->assignee_id === $user->id;
    }
}
