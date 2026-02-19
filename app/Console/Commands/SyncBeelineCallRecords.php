<?php

namespace App\Console\Commands;

use App\Services\BeelineCallRecordSyncService;
use Illuminate\Console\Command;

class SyncBeelineCallRecords extends Command
{
    protected $signature = 'beeline:sync-records
        {--mode=incremental : Режим: incremental|full|files-only|reset}
        {--from-id= : Начальный ID записи}
        {--userId= : Фильтр по идентификатору абонента}
        {--dateFrom= : Начальная дата периода}
        {--dateTo= : Конечная дата периода}
        {--max-pages=1000 : Максимум страниц (по 100 записей)}
        {--without-files : Не скачивать файлы записей}
        {--files-only : Скачивать файлы только для уже известных записей (без подтягивания новых страниц API)}
        {--files-limit=500 : Лимит записей за запуск для режима --files-only}';

    protected $description = 'Синхронизация записей звонков из Beeline CloudPBX в локальную БД';

    public function handle(BeelineCallRecordSyncService $syncService): int
    {
        $mode = mb_strtolower((string) $this->option('mode'));
        if (! in_array($mode, ['incremental', 'full', 'files-only', 'reset'], true)) {
            $this->error('Неверный --mode. Допустимо: incremental|full|files-only|reset');

            return self::FAILURE;
        }

        if ($mode === 'reset') {
            $result = $syncService->resetStoredData();
            $this->info('Хранилище Beeline очищено.');
            $this->line('Удалено записей в БД: '.($result['records_deleted'] ?? 0));
            $this->line('Удалено файлов записи: '.($result['files_deleted'] ?? 0));

            return self::SUCCESS;
        }

        $fromIdOption = $this->option('from-id');
        $fromId = null;

        if ($fromIdOption !== null && $fromIdOption !== '') {
            if (! is_numeric($fromIdOption) || (int) $fromIdOption < 1) {
                $this->error('Параметр --from-id должен быть положительным числом.');

                return self::FAILURE;
            }

            $fromId = (int) $fromIdOption;
        }

        $maxPages = (int) $this->option('max-pages');
        if ($maxPages < 1) {
            $maxPages = 1;
        }

        $downloadFiles = ! (bool) $this->option('without-files');
        $filesOnly = $mode === 'files-only' || (bool) $this->option('files-only');

        if ($filesOnly) {
            $fromId = null;
            $maxPages = 1;
        }

        try {
            if ($filesOnly) {
                $filesLimit = max(1, (int) $this->option('files-limit'));
                $result = $syncService->syncFilesForStoredRecords($filesLimit);
            } elseif ($mode === 'incremental') {
                $result = $syncService->syncIncrementalForConfiguredScope($downloadFiles, $maxPages);
            } else {
                $result = $syncService->sync(
                    $fromId,
                    $this->option('userId') ?: null,
                    $this->option('dateFrom') ?: (string) config('services.beeline_cloudpbx.sync_date_from', '2026-02-19 00:00:00'),
                    $this->option('dateTo') ?: null,
                    $maxPages,
                    $downloadFiles,
                    (string) config('services.beeline_cloudpbx.sync_department', 'Отдел продаж ИТ'),
                );
            }
        } catch (\Throwable $e) {
            $this->error('Ошибка синхронизации Beeline: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info('Синхронизация завершена.');
        if (! $filesOnly && $mode !== 'incremental') {
            $this->line('Стартовый курсор ID: '.($result['started_from_id'] ?? 'null'));
            $this->line('Конечный курсор ID: '.($result['ended_with_id'] ?? 'null'));
            $this->line('Обработано страниц: '.($result['pages'] ?? 0));
            $this->line('Всего получено записей: '.($result['processed'] ?? 0));
            $this->line('Новых сохранено: '.($result['inserted'] ?? 0));
            $this->line('Пропущено (дубли/некорректные): '.($result['skipped'] ?? 0));
        } elseif ($mode === 'incremental') {
            if (($result['checked_only'] ?? false) === true) {
                $this->line('Быстрая проверка: обновление не требуется ('.($result['reason'] ?? 'skip').').');
            }

            $this->line('Обработано страниц: '.($result['pages'] ?? 0));
            $this->line('Новых сохранено: '.($result['inserted'] ?? 0));
        }

        $this->line('Файлов скачано: '.($result['files_downloaded'] ?? 0));
        $this->line('Файлов пропущено: '.($result['files_skipped'] ?? 0));
        $this->line('Ошибок скачивания файлов: '.($result['files_failed'] ?? 0));

        return self::SUCCESS;
    }
}
