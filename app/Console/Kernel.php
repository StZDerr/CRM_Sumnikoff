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
        \App\Console\Commands\PauseProjectsOnCloseDate::class,
        \App\Console\Commands\AutoCloseWorkDays::class,
        \App\Console\Commands\SyncAvitoAccounts::class,
        \App\Console\Commands\SendAvitoAlerts::class,
        \App\Console\Commands\SyncBeelineCallRecords::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Авто выставление счетов по активным проектам
        // $schedule->command('projects:update-debts')
        //     ->dailyAt('00:00')
        //     ->timezone('Europe/Moscow')
        //     ->appendOutputTo(storage_path('logs/update_project_debts.log'));

        // Авто постановка на паузу проектов с датой закрытия сегодня
        $schedule->command('projects:pause-on-close-date')
            ->dailyAt('00:00')
            ->appendOutputTo(storage_path('logs/pause_projects_on_close_date.log'));

        // Авто закрытие незавершённых рабочих дней (выполняется в 00:00)
        $schedule->command('worktime:auto-close')
            ->dailyAt('00:00')
            ->appendOutputTo(storage_path('logs/auto_close_work_days.log'));

        // Автосинхронизация данных Avito-аккаунтов каждые 10 минут.
        // Задержка 65 сек между аккаунтами соблюдает лимит Stats V2 API (1 запрос/мин).
        // withoutOverlapping(120) означает: если предыдущий запуск ещё идёт (до 2 ч), новый не стартует.
        $schedule->command('avito:sync-accounts --delay=65')
            ->everyTenMinutes()
            ->timezone('Europe/Moscow')
            ->withoutOverlapping(120)
            ->appendOutputTo(storage_path('logs/avito_sync_accounts.log'));

        // Пороговые уведомления + сводка по всем аккаунтам — 2 раза в день.
        // Работает только с данными из БД (API не вызывается).
        $schedule->command('avito:send-alerts --summary')
            ->dailyAt('09:00')
            ->timezone('Europe/Moscow')
            ->appendOutputTo(storage_path('logs/avito_alerts.log'));

        $schedule->command('avito:send-alerts --summary')
            ->dailyAt('16:00')
            ->timezone('Europe/Moscow')
            ->appendOutputTo(storage_path('logs/avito_alerts.log'));

        // Автосинхронизация записей звонков Beeline (каждую минуту, инкрементально)
        $schedule->command('beeline:sync-records --mode=incremental')
            ->everyMinute()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/beeline_sync_records.log'));

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
