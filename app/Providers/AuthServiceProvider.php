<?php

namespace App\Providers;

use App\Models\Project;
use App\Models\RecurringTask;
use App\Models\Task;
use App\Policies\ProjectPolicy;
use App\Policies\RecurringTaskPolicy;
use App\Policies\TaskPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Project::class => ProjectPolicy::class,
        Task::class => TaskPolicy::class,
        RecurringTask::class => RecurringTaskPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
