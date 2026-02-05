<?php

namespace App\Policies;

use App\Models\RecurringTask;
use App\Models\User;
use App\Services\TaskPermissionService;

class RecurringTaskPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, RecurringTask $recurringTask): bool
    {
        return app(TaskPermissionService::class)->canViewRecurring($user, $recurringTask);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, RecurringTask $recurringTask): bool
    {
        return app(TaskPermissionService::class)->canManageRecurring($user, $recurringTask);
    }

    public function delete(User $user, RecurringTask $recurringTask): bool
    {
        return app(TaskPermissionService::class)->canManageRecurring($user, $recurringTask);
    }
}
