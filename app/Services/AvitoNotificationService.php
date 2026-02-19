<?php

namespace App\Services;

use App\Models\AvitoAccount;
use App\Models\User;
use App\Models\UserNotification;

class AvitoNotificationService
{
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
        $spendingToday = (float) data_get($stats, 'spending_today', 0);

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
            $isBreached = $spendingToday > $maxDailySpending;

            if ($isBreached) {
                $this->sendHighSpendingAlert($account, $spendingToday, $maxDailySpending);
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
        $this->sendToRecipients(
            $account,
            type: 'avito.spending.high',
            title: 'Превышение трат Avito за день',
            message: sprintf(
                'Траты за день по аккаунту «%s» превысили порог: %s ₽ > %s ₽.',
                $account->label,
                $this->formatMoney($spendingToday),
                $this->formatMoney($threshold)
            ),
            data: [
                'event' => 'avito_spending_high',
                'avito_account_id' => $account->id,
                'metric' => 'spending_today',
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
}
