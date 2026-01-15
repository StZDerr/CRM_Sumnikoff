<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use App\Models\Project;
use App\Observers\ProjectObserver;

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

        // Регистрация observer для отслеживания истории маркетологов
        Project::observe(ProjectObserver::class);
    }
}
