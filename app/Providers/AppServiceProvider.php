<?php

namespace App\Providers;

use App\Models\BeelineCallRecord;
use App\Models\Project;
use App\Observers\BeelineCallRecordObserver;
use App\Observers\ProjectObserver;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register admin middleware alias
        Route::aliasMiddleware('admin', \App\Http\Middleware\AdminMiddleware::class);
        Route::aliasMiddleware('role', \App\Http\Middleware\CheckRole::class);

        // Регистрация observer для отслеживания истории маркетологов
        Project::observe(ProjectObserver::class);
        BeelineCallRecord::observe(BeelineCallRecordObserver::class);

        // Policies
        \Illuminate\Support\Facades\Gate::policy(Project::class, \App\Policies\ProjectPolicy::class);
    }
}
