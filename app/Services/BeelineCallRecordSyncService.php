<?php

namespace App\Services;

use App\Models\BeelineCallRecord;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class BeelineCallRecordSyncService
{
    public function __construct(protected BeelineCloudPbxService $beelineCloudPbxService) {}

    public function sync(
        ?int $startId = null,
        ?string $userId = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        int $maxPages = 1000,
        bool $downloadRecordFiles = true,
        ?string $department = null,
    ): array {
        $pages = 0;
        $processed = 0;
        $inserted = 0;
        $skipped = 0;
        $filesDownloaded = 0;
        $filesSkipped = 0;
        $filesFailed = 0;

        $cursor = $startId;
        if ($cursor === null) {
            $maxKnownId = (int) (BeelineCallRecord::query()->max('beeline_id_int') ?? 0);
            $cursor = $maxKnownId > 0 ? $maxKnownId : null;
        }

        $startedFrom = $cursor;
        $endedWith = $cursor;

        while ($pages < $maxPages) {
            $batch = $this->beelineCloudPbxService->getRecords($cursor, $userId, null, $dateTo);
            if (empty($batch)) {
                break;
            }

            $pages++;
            $processed += count($batch);

            $batchIds = collect($batch)
                ->map(fn (array $record) => (string) data_get($record, 'id', ''))
                ->filter()
                ->unique()
                ->values()
                ->all();

            $existingIds = BeelineCallRecord::query()
                ->whereIn('beeline_id', $batchIds)
                ->pluck('beeline_id')
                ->all();

            $existingRecordsById = BeelineCallRecord::query()
                ->whereIn('beeline_id', $batchIds)
                ->get()
                ->keyBy('beeline_id');

            $existingLookup = array_fill_keys($existingIds, true);
            $lastNumericId = null;

            foreach ($batch as $record) {
                $recordIdRaw = data_get($record, 'id');
                if (is_numeric($recordIdRaw)) {
                    $lastNumericId = (int) $recordIdRaw;
                }

                if (! $this->isRecordInScope($record, $dateFrom, $department)) {
                    continue;
                }

                $recordId = (string) $recordIdRaw;
                if ($recordId === '') {
                    $skipped++;

                    continue;
                }

                $recordIdInt = is_numeric($recordIdRaw) ? (int) $recordIdRaw : null;

                if (isset($existingLookup[$recordId])) {
                    if ($downloadRecordFiles) {
                        $existingRecord = $existingRecordsById->get($recordId);
                        if ($existingRecord) {
                            $fileState = $this->syncRecordFile($existingRecord);
                            if ($fileState === 'downloaded') {
                                $filesDownloaded++;
                            } elseif ($fileState === 'failed') {
                                $filesFailed++;
                            } else {
                                $filesSkipped++;
                            }
                        }
                    }

                    $skipped++;

                    continue;
                }

                try {
                    $created = BeelineCallRecord::query()->create([
                        'beeline_id' => $recordId,
                        'beeline_id_int' => $recordIdInt,
                        'external_id' => data_get($record, 'externalId'),
                        'call_id' => data_get($record, 'callId'),
                        'phone' => data_get($record, 'phone'),
                        'direction' => data_get($record, 'direction'),
                        'call_date' => $this->normalizeDateTime(data_get($record, 'date')),
                        'duration_ms' => $this->normalizeUnsignedBigInt(data_get($record, 'duration')),
                        'file_size' => $this->normalizeUnsignedBigInt(data_get($record, 'fileSize')),
                        'comment' => data_get($record, 'comment'),
                        'abonent_user_id' => data_get($record, 'abonent.userId'),
                        'abonent_phone' => data_get($record, 'abonent.phone'),
                        'abonent_first_name' => data_get($record, 'abonent.firstName'),
                        'abonent_last_name' => data_get($record, 'abonent.lastName'),
                        'abonent_email' => data_get($record, 'abonent.email'),
                        'abonent_contact_email' => data_get($record, 'abonent.contactEmail'),
                        'abonent_department' => data_get($record, 'abonent.department'),
                        'abonent_extension' => data_get($record, 'abonent.extension'),
                        'raw_payload' => $record,
                        'synced_at' => now(),
                    ]);

                    $inserted++;

                    if ($downloadRecordFiles) {
                        $fileState = $this->syncRecordFile($created);
                        if ($fileState === 'downloaded') {
                            $filesDownloaded++;
                        } elseif ($fileState === 'failed') {
                            $filesFailed++;
                        } else {
                            $filesSkipped++;
                        }
                    }
                } catch (QueryException $e) {
                    if ($this->isDuplicateKeyError($e)) {
                        $skipped++;

                        continue;
                    }

                    throw $e;
                }
            }

            if (count($batch) < 100) {
                break;
            }

            if ($lastNumericId === null) {
                break;
            }

            $cursor = $lastNumericId;
            $endedWith = $cursor;
        }

        return [
            'started_from_id' => $startedFrom,
            'ended_with_id' => $endedWith,
            'pages' => $pages,
            'processed' => $processed,
            'inserted' => $inserted,
            'skipped' => $skipped,
            'files_downloaded' => $filesDownloaded,
            'files_skipped' => $filesSkipped,
            'files_failed' => $filesFailed,
        ];
    }

    public function syncIncrementalForConfiguredScope(bool $downloadRecordFiles = true, int $maxPages = 1000): array
    {
        $dateFrom = (string) config('services.beeline_cloudpbx.sync_date_from', '2026-02-19 00:00:00');
        $department = (string) config('services.beeline_cloudpbx.sync_department', 'Отдел продаж ИТ');

        $maxKnownId = (int) (BeelineCallRecord::query()->max('beeline_id_int') ?? 0);
        $cursor = $maxKnownId > 0 ? $maxKnownId : null;

        $firstBatch = $this->beelineCloudPbxService->getRecords($cursor, null, null, null);
        if (empty($firstBatch)) {
            return [
                'checked_only' => true,
                'reason' => 'no_new_data',
                'pages' => 1,
                'processed' => 0,
                'inserted' => 0,
                'skipped' => 0,
                'files_downloaded' => 0,
                'files_skipped' => 0,
                'files_failed' => 0,
            ];
        }

        $firstScoped = collect($firstBatch)->first(fn (array $record) => $this->isRecordInScope($record, $dateFrom, $department));

        $firstRecordId = (string) data_get($firstBatch, '0.id', '');
        if ($firstRecordId !== '' && BeelineCallRecord::query()->where('beeline_id', $firstRecordId)->exists()) {
            return [
                'checked_only' => true,
                'reason' => 'first_record_already_exists',
                'first_id' => $firstRecordId,
                'pages' => 1,
                'processed' => 0,
                'inserted' => 0,
                'skipped' => 0,
                'files_downloaded' => 0,
                'files_skipped' => 0,
                'files_failed' => 0,
            ];
        }

        if (! $firstScoped) {
            return [
                'checked_only' => true,
                'reason' => 'no_data_in_scope',
                'pages' => 1,
                'processed' => count($firstBatch),
                'inserted' => 0,
                'skipped' => 0,
                'files_downloaded' => 0,
                'files_skipped' => 0,
                'files_failed' => 0,
            ];
        }

        $pages = 0;
        $processed = 0;
        $inserted = 0;
        $skipped = 0;
        $filesDownloaded = 0;
        $filesSkipped = 0;
        $filesFailed = 0;
        if ($maxKnownId <= 0) {
            $cursor = null;
        }

        while ($pages < $maxPages) {
            $batch = $this->beelineCloudPbxService->getRecords($cursor, null, null, null);
            if (empty($batch)) {
                break;
            }

            $pages++;
            $processed += count($batch);

            $batchIds = collect($batch)
                ->map(fn (array $record) => (string) data_get($record, 'id', ''))
                ->filter()
                ->values()
                ->all();

            $existingLookup = array_fill_keys(
                BeelineCallRecord::query()->whereIn('beeline_id', $batchIds)->pluck('beeline_id')->all(),
                true
            );

            $lastNumericId = null;
            $stopByExisting = false;

            foreach ($batch as $record) {
                $recordIdRaw = data_get($record, 'id');
                $recordId = (string) $recordIdRaw;

                if (is_numeric($recordIdRaw)) {
                    $lastNumericId = (int) $recordIdRaw;
                }

                if (! $this->isRecordInScope($record, $dateFrom, $department)) {
                    continue;
                }

                if ($recordId !== '' && isset($existingLookup[$recordId])) {
                    $stopByExisting = true;
                    break;
                }

                if ($recordId === '') {
                    $skipped++;

                    continue;
                }

                $created = BeelineCallRecord::query()->create([
                    'beeline_id' => $recordId,
                    'beeline_id_int' => is_numeric($recordIdRaw) ? (int) $recordIdRaw : null,
                    'external_id' => data_get($record, 'externalId'),
                    'call_id' => data_get($record, 'callId'),
                    'phone' => data_get($record, 'phone'),
                    'direction' => data_get($record, 'direction'),
                    'call_date' => $this->normalizeDateTime(data_get($record, 'date')),
                    'duration_ms' => $this->normalizeUnsignedBigInt(data_get($record, 'duration')),
                    'file_size' => $this->normalizeUnsignedBigInt(data_get($record, 'fileSize')),
                    'comment' => data_get($record, 'comment'),
                    'abonent_user_id' => data_get($record, 'abonent.userId'),
                    'abonent_phone' => data_get($record, 'abonent.phone'),
                    'abonent_first_name' => data_get($record, 'abonent.firstName'),
                    'abonent_last_name' => data_get($record, 'abonent.lastName'),
                    'abonent_email' => data_get($record, 'abonent.email'),
                    'abonent_contact_email' => data_get($record, 'abonent.contactEmail'),
                    'abonent_department' => data_get($record, 'abonent.department'),
                    'abonent_extension' => data_get($record, 'abonent.extension'),
                    'raw_payload' => $record,
                    'synced_at' => now(),
                ]);

                $inserted++;

                if ($downloadRecordFiles) {
                    $fileState = $this->syncRecordFile($created);
                    if ($fileState === 'downloaded') {
                        $filesDownloaded++;
                    } elseif ($fileState === 'failed') {
                        $filesFailed++;
                    } else {
                        $filesSkipped++;
                    }
                }
            }

            if ($stopByExisting || count($batch) < 100 || $lastNumericId === null) {
                break;
            }

            $cursor = $lastNumericId;
        }

        return [
            'checked_only' => false,
            'pages' => $pages,
            'processed' => $processed,
            'inserted' => $inserted,
            'skipped' => $skipped,
            'files_downloaded' => $filesDownloaded,
            'files_skipped' => $filesSkipped,
            'files_failed' => $filesFailed,
        ];
    }

    public function resetStoredData(): array
    {
        $recordsDeleted = BeelineCallRecord::query()->count();
        BeelineCallRecord::query()->truncate();

        $filesDeleted = 0;
        $disk = Storage::disk('local');
        if ($disk->exists('beeline/records')) {
            $filesDeleted = count($disk->allFiles('beeline/records'));
            $disk->deleteDirectory('beeline/records');
        }

        return [
            'records_deleted' => $recordsDeleted,
            'files_deleted' => $filesDeleted,
        ];
    }

    public function syncFilesForStoredRecords(int $limit = 500): array
    {
        $filesDownloaded = 0;
        $filesSkipped = 0;
        $filesFailed = 0;

        BeelineCallRecord::query()
            ->where(function ($q) {
                $q->whereNull('record_file_downloaded_at')
                    ->orWhereNull('record_file_path');
            })
            ->orderByDesc('beeline_id_int')
            ->limit($limit)
            ->get()
            ->each(function (BeelineCallRecord $record) use (&$filesDownloaded, &$filesSkipped, &$filesFailed) {
                $state = $this->syncRecordFile($record);
                if ($state === 'downloaded') {
                    $filesDownloaded++;
                } elseif ($state === 'failed') {
                    $filesFailed++;
                } else {
                    $filesSkipped++;
                }
            });

        return [
            'files_downloaded' => $filesDownloaded,
            'files_skipped' => $filesSkipped,
            'files_failed' => $filesFailed,
        ];
    }

    protected function syncRecordFile(BeelineCallRecord $record): string
    {
        if ($record->record_file_downloaded_at && $record->record_file_path && Storage::disk('local')->exists($record->record_file_path)) {
            return 'skipped';
        }

        try {
            $download = $this->beelineCloudPbxService->downloadRecordFile($record->beeline_id);

            $content = (string) data_get($download, 'content', '');
            if ($content === '') {
                $this->markRecordFileError($record, 'Пустой контент файла записи');

                return 'failed';
            }

            $fileName = (string) data_get($download, 'file_name', $record->beeline_id.'.bin');
            $mimeType = (string) data_get($download, 'mime_type', 'application/octet-stream');
            $path = 'beeline/records/'.$fileName;

            Storage::disk('local')->put($path, $content);

            $record->record_file_path = $path;
            $record->record_file_mime = $mimeType;
            $record->record_file_local_size = strlen($content);
            $record->record_file_sha1 = sha1($content);
            $record->record_file_downloaded_at = now();
            $record->record_file_error = null;
            $record->save();

            return 'downloaded';
        } catch (\Throwable $e) {
            $this->markRecordFileError($record, $e->getMessage());

            return 'failed';
        }
    }

    protected function markRecordFileError(BeelineCallRecord $record, string $message): void
    {
        $record->record_file_error = mb_substr($message, 0, 5000);
        $record->save();
    }

    protected function normalizeDateTime(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            if (is_numeric($value)) {
                $timestamp = (int) $value;
                $seconds = $timestamp > 10000000000 ? intdiv($timestamp, 1000) : $timestamp;

                return Carbon::createFromTimestamp($seconds)->toDateTimeString();
            }

            return Carbon::parse((string) $value)->toDateTimeString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function normalizeUnsignedBigInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        $number = (int) $value;

        return $number >= 0 ? $number : null;
    }

    protected function isDuplicateKeyError(QueryException $e): bool
    {
        if ((string) $e->getCode() === '23000') {
            return true;
        }

        $message = mb_strtolower($e->getMessage());

        return str_contains($message, 'duplicate') || str_contains($message, 'unique');
    }

    protected function isRecordInScope(array $record, ?string $dateFrom, ?string $department): bool
    {
        if ($department !== null && $department !== '') {
            $dep = (string) data_get($record, 'abonent.department', '');
            if ($dep !== $department) {
                return false;
            }
        }

        if ($dateFrom !== null && $dateFrom !== '') {
            $recordDate = $this->normalizeDateTime(data_get($record, 'date'));
            if (! $recordDate) {
                return false;
            }

            try {
                return Carbon::parse($recordDate)->gte(Carbon::parse($dateFrom));
            } catch (\Throwable $e) {
                return false;
            }
        }

        return true;
    }
}
