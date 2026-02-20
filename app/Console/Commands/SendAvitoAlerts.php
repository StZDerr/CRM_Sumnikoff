<?php

namespace App\Console\Commands;

use App\Models\AvitoAccount;
use App\Services\AvitoNotificationService;
use Illuminate\Console\Command;

/**
 * Отправляет пороговые уведомления по аккаунтам Avito.
 *
 * Команда читает только данные из БД (без обращений к Avito API),
 * поэтому работает мгновенно и не зависит от лимитов API.
 *
 * Расписание: 2 раза в день — в 09:00 и 16:00 по МСК.
 */
class SendAvitoAlerts extends Command
{
    protected $signature = 'avito:send-alerts
                            {--summary : Отправить сводку по всем аккаунтам в дополнение к пороговым алертам}';

    protected $description = 'Send threshold notifications for all active Avito accounts (reads from DB, no API calls)';

    public function handle(AvitoNotificationService $notificationService): int
    {
        $sendSummary = (bool) $this->option('summary');

        $accounts = AvitoAccount::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        if ($accounts->isEmpty()) {
            $this->info('Нет активных аккаунтов.');
            return self::SUCCESS;
        }

        $alertsSent = 0;
        $summaryLines = [];

        foreach ($accounts as $account) {
            /** @var AvitoAccount $account */
            $sent = $notificationService->processThresholdAlerts($account);
            $alertsSent += $sent;

            if ($sendSummary) {
                $stats = $account->stats_data ?? [];
                $advance = (float) ($stats['advance'] ?? 0);
                $spending = (float) ($stats['spending_today'] ?? 0);
                $spendingPerDay = (float) ($stats['spending_per_day'] ?? 0);
                $views = (int) ($stats['views_today'] ?? 0);
                $contacts = (int) ($stats['contacts_today'] ?? 0);

                $summaryLines[] = sprintf(
                    "• %s\n  Аванс: %s ₽ | Траты сегодня: %s ₽ | Ср./день: %s ₽ | Просмотры: %d | Контакты: %d",
                    $account->label,
                    number_format($advance, 0, ',', ' '),
                    number_format($spending, 2, ',', ' '),
                    number_format($spendingPerDay, 0, ',', ' '),
                    $views,
                    $contacts
                );
            }
        }

        if ($sendSummary && !empty($summaryLines)) {
            $text = sprintf(
                "📊 Сводка Avito — %s\n\n%s",
                now()->timezone('Europe/Moscow')->format('d.m.Y H:i'),
                implode("\n\n", $summaryLines)
            );
            $notificationService->sendSummaryTelegram($text);
        }

        $this->info("Готово. Уведомлений отправлено: {$alertsSent}, аккаунтов проверено: {$accounts->count()}");

        return self::SUCCESS;
    }
}
