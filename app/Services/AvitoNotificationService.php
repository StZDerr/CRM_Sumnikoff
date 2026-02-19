<?php

namespace App\Services;

use App\Models\AvitoAccount;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AvitoNotificationService
{
    public function sendTestTelegramMessage(?string $message = null): array
    {
        $text = trim((string) $message);
        if ($text === '') {
            $text = '🧪 Тестовое сообщение Avito-бота '.now()->format('d.m.Y H:i:s');
        }

        return $this->sendTelegramMessage($text);
    }

    public function processThresholdAlerts(AvitoAccount $account): void
    {
        $settings = $account->notification_settings ?? [];
        $minAdvance = $this->normalizeThreshold(data_get($settings, 'min_advance'));
        $maxDailySpending = $this->normalizeThreshold(data_get($settings, 'max_daily_spending'));

        if ($minAdvance === null && $maxDailySpending === null) {
            return;
        }

        $stats = $account->stats_data ?? [];
        $advance = (float) data_get($stats, 'advance', 0);
        $spendingPerDay = (float) data_get($stats, 'spending_per_day', data_get($stats, 'spending_today', 0));

        $state = $account->notification_state ?? [];

        if ($minAdvance !== null) {
            $isBreached = $advance < $minAdvance;

            if ($isBreached) {
                $this->sendLowAdvanceAlert($account, $advance, $minAdvance);
                $state['advance_alert_last_sent_at'] = now()->toDateTimeString();
            }

            $state['advance_alert_active'] = $isBreached;
        } else {
            $state['advance_alert_active'] = false;
        }

        if ($maxDailySpending !== null) {
            $isBreached = $spendingPerDay >= $maxDailySpending;

            if ($isBreached) {
                $this->sendHighSpendingAlert($account, $spendingPerDay, $maxDailySpending);
                $state['spending_alert_last_sent_at'] = now()->toDateTimeString();
            }

            $state['spending_alert_active'] = $isBreached;
        } else {
            $state['spending_alert_active'] = false;
        }

        $account->notification_state = $state;
        $account->save();
    }

    protected function sendLowAdvanceAlert(AvitoAccount $account, float $currentAdvance, float $threshold): void
    {
        $account->loadMissing('project.marketer');
        $responsibleName = $this->resolveResponsibleName($account);

        $telegramMessage = sprintf(
            "Ответственная: %s\n⚠️ ВНИМАНИЕ! У вас Аванс по аккаунту «%s» ниже порога: %s ₽ < %s ₽.",
            $responsibleName,
            $account->label,
            $this->formatMoney($currentAdvance),
            $this->formatMoney($threshold)
        );

        $this->sendTelegramMessage($telegramMessage);

        $this->sendToRecipients(
            $account,
            type: 'avito.advance.low',
            title: 'Низкий аванс Avito',
            message: sprintf(
                'Аванс по аккаунту «%s» ниже порога: %s ₽ < %s ₽.',
                $account->label,
                $this->formatMoney($currentAdvance),
                $this->formatMoney($threshold)
            ),
            data: [
                'event' => 'avito_advance_low',
                'avito_account_id' => $account->id,
                'metric' => 'advance',
                'current_value' => $currentAdvance,
                'threshold_value' => $threshold,
            ]
        );
    }

    protected function sendHighSpendingAlert(AvitoAccount $account, float $spendingToday, float $threshold): void
    {
        $account->loadMissing('project.marketer');
        $responsibleName = $this->resolveResponsibleName($account);

        $telegramMessage = sprintf(
            "Ответственная: %s\n⚠️ ВНИМАНИЕ! У вас Траты/день по аккаунту «%s» выше порога: %s ₽ > %s ₽.",
            $responsibleName,
            $account->label,
            $this->formatMoney($spendingToday),
            $this->formatMoney($threshold)
        );

        $this->sendTelegramMessage($telegramMessage);

        $this->sendToRecipients(
            $account,
            type: 'avito.spending.high',
            title: 'Превышение трат Avito за день',
            message: sprintf(
                'Траты/день по аккаунту «%s» достигли/превысили порог: %s ₽ >= %s ₽.',
                $account->label,
                $this->formatMoney($spendingToday),
                $this->formatMoney($threshold)
            ),
            data: [
                'event' => 'avito_spending_high',
                'avito_account_id' => $account->id,
                'metric' => 'spending_per_day',
                'current_value' => $spendingToday,
                'threshold_value' => $threshold,
            ]
        );
    }

    protected function sendToRecipients(AvitoAccount $account, string $type, string $title, string $message, array $data): void
    {
        $account->loadMissing('project.marketer');

        $recipientIds = User::query()
            ->where('role', User::ROLE_ADMIN)
            ->pluck('id')
            ->all();

        $marketerId = data_get($account, 'project.marketer_id');
        if ($marketerId) {
            $recipientIds[] = (int) $marketerId;
        }

        $recipientIds = array_values(array_unique(array_map('intval', $recipientIds)));

        if (empty($recipientIds)) {
            return;
        }

        $rows = [];
        foreach ($recipientIds as $recipientId) {
            $rows[] = [
                'user_id' => $recipientId,
                'actor_id' => null,
                'project_id' => $account->project_id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
                'target_role' => null,
                'target_position' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        UserNotification::query()->insert($rows);
    }

    protected function normalizeThreshold(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        return round((float) $value, 2);
    }

    protected function formatMoney(float $amount): string
    {
        return number_format($amount, 2, ',', ' ');
    }

    protected function resolveResponsibleName(AvitoAccount $account): string
    {
        $name = trim((string) data_get($account, 'project.marketer.name', ''));

        return $name !== '' ? $name : 'не назначена';
    }

    protected function sendTelegramMessage(string $message): array
    {
        $token = (string) config('avito.telegram.bot_token');
        $chatId = (string) config('avito.telegram.chat_id');
        $threadId = config('avito.telegram.message_thread_id');

        if ($token === '' || $chatId === '') {
            return [
                'sent' => false,
                'error' => 'Не заполнены AVITO_TELEGRAM_BOT_TOKEN или AVITO_TELEGRAM_CHAT_ID в .env',
            ];
        }

        try {
            $payload = [
                'chat_id' => $chatId,
                'text' => $message,
            ];

            if ($threadId !== null && $threadId !== '' && is_numeric($threadId)) {
                $payload['message_thread_id'] = (int) $threadId;
            }

            $response = Http::asForm()->post("https://api.telegram.org/bot{$token}/sendMessage", $payload);

            if (! $response->ok()) {
                Log::warning('Avito telegram notification failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'sent' => false,
                    'status' => $response->status(),
                    'error' => $response->body(),
                ];
            }

            return [
                'sent' => true,
                'status' => $response->status(),
            ];
        } catch (\Throwable $e) {
            Log::warning('Avito telegram notification exception', [
                'error' => $e->getMessage(),
            ]);

            return [
                'sent' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
