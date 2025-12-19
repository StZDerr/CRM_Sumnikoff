<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Список дополнительных Artisan-команд приложения.
     *
     * @var array<int, class-string>
     */
    protected $commands = [
        \App\Console\Commands\UpdateProjectDebts::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Выполняем команду пересчёта долгов ежедневно в 01:00 (использует временную зону приложения)
        $schedule->command('projects:update-debts')
            ->dailyAt('00:00')
            ->timezone('Europe/Moscow')
            ->appendOutputTo(storage_path('logs/update_project_debts.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        // автозагрузка команд в папке app/Console/Commands
        $this->load(__DIR__.'/Commands');

        // загрузка маршрутов console (если нужны)
        require base_path('routes/console.php');
    }
}
