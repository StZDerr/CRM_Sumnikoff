<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RegApiClient
{
    public function __construct(
        protected string $baseUrl,
        protected string $username,
        protected string $password
    ) {}

    public static function make(): self
    {
        return new self(
            rtrim((string) config('regapi.base_url'), '/'),
            (string) config('regapi.username'),
            (string) config('regapi.password')
        );
    }

    public function getDomains(): array
    {
        $response = Http::asForm()->post($this->baseUrl.'/service/get_list', [
            'username' => $this->username,
            'password' => $this->password,
            'output_format' => 'json',
            'servtype' => 'domain',
        ]);

        if (! $response->ok()) {
            throw new \RuntimeException('REG.API HTTP error: '.$response->status());
        }

        $data = $response->json();

        if (($data['result'] ?? 'error') !== 'success') {
            $errorText = $data['error_text'] ?? 'Unknown REG.API error';
            $errorCode = $data['error_code'] ?? 'UNKNOWN';
            throw new \RuntimeException("REG.API error {$errorCode}: {$errorText}");
        }

        return $data['answer']['services'] ?? [];
    }
}
