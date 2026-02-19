<?php

namespace App\Http\Controllers;

use App\Models\BeelineCallRecord;
use App\Services\BeelineCloudPbxService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BilainController extends Controller
{
    public function index(Request $request)
    {
        $validated = $this->validateRecordsParams($request);
        $syncDateFrom = (string) config('services.beeline_cloudpbx.sync_date_from', '2026-02-19 00:00:00');

        $effectiveDateFrom = data_get($validated, 'dateFrom') ?: $syncDateFrom;

        $query = BeelineCallRecord::query()
            ->when($effectiveDateFrom, fn ($q, $dateFrom) => $q->where('call_date', '>=', Carbon::parse($dateFrom)->toDateTimeString()))
            ->when(data_get($validated, 'dateTo'), fn ($q, $dateTo) => $q->where('call_date', '<=', Carbon::parse($dateTo)->toDateTimeString()));


        $callsCount = (clone $query)->count();
        $totalDurationMs = (int) ((clone $query)->sum('duration_ms') ?? 0);
        $avgDurationMs = $callsCount > 0 ? (int) round($totalDurationMs / $callsCount) : 0;

        $records = $query
            ->orderByDesc('beeline_id_int')
            ->orderByDesc('id')
            ->paginate(100)
            ->withQueryString();

        $records->setCollection(
            $records->getCollection()->map(function (BeelineCallRecord $record) {
                return [
                    'dbId' => $record->id,
                    'id' => $record->beeline_id,
                    'externalId' => $record->external_id,
                    'callId' => $record->call_id,
                    'date' => $record->call_date?->toDateTimeString(),
                    'formattedDate' => $record->call_date?->format('d.m.Y H:i:s'),
                    'direction' => $record->direction,
                    'directionLabel' => $this->translateDirection($record->direction),
                    'phone' => $record->phone,
                    'duration' => $record->duration_ms,
                    'durationMinutes' => round(((float) ($record->duration_ms ?? 0)) / 60000, 2),
                    'fileSize' => $record->file_size,
                    'recordFilePath' => $record->record_file_path,
                    'recordFileDownloadedAt' => $record->record_file_downloaded_at?->format('d.m.Y H:i:s'),
                    'recordFileError' => $record->record_file_error,
                    'comment' => $record->comment,
                    'abonent' => [
                        'userId' => $record->abonent_user_id,
                        'phone' => $record->abonent_phone,
                        'firstName' => $record->abonent_first_name,
                        'lastName' => $record->abonent_last_name,
                        'email' => $record->abonent_email,
                        'contactEmail' => $record->abonent_contact_email,
                        'department' => $record->abonent_department,
                        'extension' => $record->abonent_extension,
                    ],
                ];
            })
        );

        return view('admin.bilain.index', [
            'records' => $records,
            'error' => null,
            'filters' => [
                'dateFrom' => $effectiveDateFrom,
                'dateTo' => data_get($validated, 'dateTo'),
            ],
            'stats' => [
                'callsCount' => $callsCount,
                'totalDurationMs' => $totalDurationMs,
                'avgDurationMs' => $avgDurationMs,
                'totalDurationHuman' => $this->formatMsToMinutesSeconds($totalDurationMs),
                'avgDurationHuman' => $this->formatMsToMinutesSeconds($avgDurationMs),
            ],
        ]);
    }

    public function records(Request $request, BeelineCloudPbxService $beelineCloudPbxService): JsonResponse
    {
        $validated = $this->validateRecordsParams($request);

        try {
            $records = $beelineCloudPbxService->getRecords(
                null,
                null,
                data_get($validated, 'dateFrom'),
                data_get($validated, 'dateTo'),
            );

            return response()->json($records);
        } catch (HttpException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getStatusCode());
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Не удалось получить записи звонков Beeline.',
            ], 500);
        }
    }

    public function downloadStoredRecordFile(BeelineCallRecord $record)
    {
        if (! $record->record_file_path || ! Storage::disk('local')->exists($record->record_file_path)) {
            return redirect()->route('bilain.index')->with('error', 'Файл записи не найден в локальном хранилище.');
        }

        return response()->download(
            Storage::disk('local')->path($record->record_file_path),
            basename($record->record_file_path),
            [
                'Content-Type' => $record->record_file_mime ?: 'application/octet-stream',
            ]
        );
    }

    public function streamStoredRecordFile(BeelineCallRecord $record)
    {
        if (! $record->record_file_path || ! Storage::disk('local')->exists($record->record_file_path)) {
            abort(404, 'Файл записи не найден в локальном хранилище.');
        }

        $absolutePath = Storage::disk('local')->path($record->record_file_path);
        $mimeType = $this->resolveAudioMimeType($record->record_file_mime, $absolutePath);

        return response()->file($absolutePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="'.basename($record->record_file_path).'"',
        ]);
    }

    protected function validateRecordsParams(Request $request): array
    {
        return $request->validate([
            'dateFrom' => ['nullable', 'date'],
            'dateTo' => ['nullable', 'date', 'after_or_equal:dateFrom'],
        ]);
    }

    protected function formatDateTime(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            // Если приходит числовой таймстамп — поддерживаем секунды и миллисекунды
            if (is_numeric($value)) {
                $v = (int) $value;
                // Значения > 10_000_000_000 считаем миллисекундами (13+ цифр)
                $seconds = $v > 10000000000 ? intdiv($v, 1000) : $v;

                return Carbon::createFromTimestamp($seconds)->format('d.m.Y H:i:s');
            }

            return Carbon::parse($value)->format('d.m.Y H:i:s');
        } catch (\Throwable $e) {
            return $value;
        }
    }

    protected function formatMsToMinutesSeconds(int $ms): string
    {
        $seconds = intdiv($ms, 1000);
        $minutes = intdiv($seconds, 60);
        $secs = $seconds % 60;

        return sprintf('%d:%02d', $minutes, $secs);
    }

    protected function translateDirection(?string $direction): string
    {
        if (! $direction) {
            return '—';
        }

        $d = mb_strtoupper(trim((string) $direction));

        switch ($d) {
            case 'INBOUND':
                return 'Входящий';
            case 'OUTBOUND':
                return 'Исходящий';
            default:
                return $direction;
        }
    }

    protected function resolveAudioMimeType(?string $storedMimeType, string $absolutePath): string
    {
        $storedMimeType = trim((string) $storedMimeType);
        if (str_starts_with(mb_strtolower($storedMimeType), 'audio/')) {
            return $storedMimeType;
        }

        $header = '';
        $fp = @fopen($absolutePath, 'rb');
        if (is_resource($fp)) {
            $header = (string) fread($fp, 16);
            fclose($fp);
        }

        if (str_starts_with($header, 'ID3') || (strlen($header) >= 2 && ord($header[0]) === 0xFF && (ord($header[1]) & 0xE0) === 0xE0)) {
            return 'audio/mpeg';
        }

        if (str_starts_with($header, 'RIFF') && substr($header, 8, 4) === 'WAVE') {
            return 'audio/wav';
        }

        if (str_starts_with($header, 'OggS')) {
            return 'audio/ogg';
        }

        if (substr($header, 4, 4) === 'ftyp') {
            return 'audio/mp4';
        }

        return 'application/octet-stream';
    }
}
