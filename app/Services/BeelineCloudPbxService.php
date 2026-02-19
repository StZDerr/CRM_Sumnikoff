<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BeelineCloudPbxService
{
    public function getRecords(?int $id = null, ?string $userId = null, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $token = trim((string) config('services.beeline_cloudpbx.api_token', ''));

        if ($token === '') {
            throw new HttpException(500, 'Не задан токен Beeline CloudPBX API. Укажите BEELINE_CLOUDPBX_API_TOKEN в .env');
        }

        $query = array_filter([
            'id' => $id,
            'userId' => $userId,
            'dateFrom' => $this->normalizeApiDateTime($dateFrom),
            'dateTo' => $this->normalizeApiDateTime($dateTo),
        ], static fn ($value) => $value !== null && $value !== '');

        $response = $this->authorizedRequest()
            ->acceptJson()
            ->get('/apis/portal/records', $query);

        if ($response->status() === 400) {
            $errorType = (string) data_get($response->json() ?? [], 'error', 'BadRequest');
            throw new HttpException(400, 'Ошибка запроса к Beeline CloudPBX: '.$errorType);
        }

        if (! $response->ok()) {
            throw new HttpException($response->status(), 'Ошибка Beeline CloudPBX API: HTTP '.$response->status());
        }

        $payload = $response->json();

        return is_array($payload) ? $payload : [];
    }

    public function getRecordById(string $recordId): array
    {
        $response = $this->authorizedRequest()
            ->acceptJson()
            ->get('/apis/portal/records/'.urlencode($recordId));

        if ($response->status() === 400) {
            $errorType = (string) data_get($response->json() ?? [], 'error', 'BadRequest');
            throw new HttpException(400, 'Ошибка запроса к Beeline CloudPBX: '.$errorType);
        }

        if (! $response->ok()) {
            throw new HttpException($response->status(), 'Ошибка Beeline CloudPBX API: HTTP '.$response->status());
        }

        $payload = $response->json();

        return is_array($payload) ? $payload : [];
    }

    public function downloadRecordFile(string $recordId): array
    {
        $candidates = [
            '/apis/portal/v2/records/'.urlencode($recordId).'/download?recordId='.urlencode($recordId),
            '/apis/portal/records/'.urlencode($recordId).'/download',
            '/apis/portal/records/'.urlencode($recordId).'/file',
            '/apis/portal/records/'.urlencode($recordId),
        ];

        foreach ($candidates as $path) {
            $response = $this->authorizedRequest()
                ->withOptions(['stream' => true])
                ->get($path);

            if (! $response->ok()) {
                continue;
            }

            $contentType = (string) ($response->header('Content-Type') ?? '');
            $body = $response->body();

            if ($body === '') {
                continue;
            }

            if ($this->looksLikeJson($contentType, $body)) {
                $json = json_decode($body, true);
                if (is_array($json)) {
                    $url = data_get($json, 'fileUrl')
                        ?? data_get($json, 'url')
                        ?? data_get($json, 'recordUrl')
                        ?? data_get($json, 'downloadUrl');

                    if (is_string($url) && $url !== '') {
                        $download = $this->authorizedRequest()->withOptions(['stream' => true])->get($url);
                        if ($download->ok() && $download->body() !== '') {
                            return [
                                'content' => $download->body(),
                                'mime_type' => (string) ($download->header('Content-Type') ?? 'application/octet-stream'),
                                'file_name' => $this->resolveFileName($recordId, (string) ($download->header('Content-Type') ?? '')),
                            ];
                        }
                    }
                }

                continue;
            }

            return [
                'content' => $body,
                'mime_type' => $contentType !== '' ? $contentType : 'application/octet-stream',
                'file_name' => $this->resolveFileName($recordId, $contentType),
            ];
        }

        throw new HttpException(404, 'Не удалось скачать файл записи разговора recordId='.$recordId);
    }

    protected function authorizedRequest()
    {
        $token = trim((string) config('services.beeline_cloudpbx.api_token', ''));

        if ($token === '') {
            throw new HttpException(500, 'Не задан токен Beeline CloudPBX API. Укажите BEELINE_CLOUDPBX_API_TOKEN в .env');
        }

        return Http::timeout(30)
            ->baseUrl($this->baseUrl())
            ->withHeaders([
                'X-MPBX-API-AUTH-TOKEN' => $token,
            ]);
    }

    protected function resolveFileName(string $recordId, string $mimeType): string
    {
        $ext = 'bin';

        if (Str::contains($mimeType, 'mpeg') || Str::contains($mimeType, 'mp3')) {
            $ext = 'mp3';
        } elseif (Str::contains($mimeType, 'wav')) {
            $ext = 'wav';
        } elseif (Str::contains($mimeType, 'ogg')) {
            $ext = 'ogg';
        }

        return $recordId.'.'.$ext;
    }

    protected function looksLikeJson(string $contentType, string $body): bool
    {
        if (Str::contains(mb_strtolower($contentType), 'json')) {
            return true;
        }

        $trimmed = ltrim($body);

        return Str::startsWith($trimmed, '{') || Str::startsWith($trimmed, '[');
    }

    protected function normalizeApiDateTime(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->format('Y-m-d\TH:i:s');
        } catch (\Throwable $e) {
            return $value;
        }
    }

    protected function baseUrl(): string
    {
        return rtrim((string) config('services.beeline_cloudpbx.base_url', 'https://cloudpbx.beeline.ru'), '/');
    }
}
