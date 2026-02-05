<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use App\Services\TaskPermissionService;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Task $task): bool
    {
        return app(TaskPermissionService::class)->canView($user, $task);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Task $task): bool
    {
        return app(TaskPermissionService::class)->canEdit($user, $task);
    }

    public function changeStatus(User $user, Task $task): bool
    {
        return app(TaskPermissionService::class)->canChangeStatus($user, $task);
    }

    public function changeDeadline(User $user, Task $task): bool
    {
        return app(TaskPermissionService::class)->canChangeDeadline($user, $task);
    }

    public function close(User $user, Task $task): bool
    {
        return app(TaskPermissionService::class)->canClose($user, $task);
    }

    public function comment(User $user, Task $task): bool
    {
        return app(TaskPermissionService::class)->canComment($user, $task);
    }
}
