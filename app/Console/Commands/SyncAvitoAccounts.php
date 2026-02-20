<?php

namespace App\Console\Commands;

use App\Models\AvitoAccount;
use App\Services\AvitoApiService;
use Illuminate\Console\Command;

class SyncAvitoAccounts extends Command
{
    protected $signature = 'avito:sync-accounts
                            {--delay=65 : Задержка в секундах между аккаунтами для соблюдения лимита Stats V2 API (1 запрос/мин)}';

    protected $description = 'Synchronize active Avito accounts (no notifications — use avito:send-alerts for that)';

    public function handle(AvitoApiService $avitoApiService): int
    {
        $delay = max(0, (int) $this->option('delay'));
        $processed = 0;
        $failed = 0;
        $lastStatsV2CallAt = null;

        $accounts = AvitoAccount::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        $total = $accounts->count();
        if ($total === 0) {
            $this->info('Нет активных аккаунтов.');
            return self::SUCCESS;
        }

        foreach ($accounts as $index => $account) {
            /** @var AvitoAccount $account */
            // Соблюдаем лимит Stats V2: не чаще 1 запроса в минуту.
            // Задержка добавляется ПЕРЕД каждым не первым аккаунтом.
            if ($delay > 0 && $lastStatsV2CallAt !== null) {
                $elapsed = (int) $lastStatsV2CallAt->diffInSeconds(now());
                $sleepSeconds = max(0, $delay - $elapsed);
                if ($sleepSeconds > 0) {
                    $this->line("  ⏳ Ожидание {$sleepSeconds}с (лимит Stats V2 API)…");
                    sleep($sleepSeconds);
                }
            }

            try {
                $result = $avitoApiService->syncAccount($account);

                $account->oauth_data = $result['oauth_data'];
                $account->profile_data = $result['profile_data'];
                $account->stats_data = $result['stats_data'];
                $account->last_synced_at = now();
                $account->save();

                $lastStatsV2CallAt = now();
                $processed++;
                $this->line("[{$index}/{$total}] ✓ {$account->label}");
            } catch (\Throwable $e) {
                $failed++;
                $lastStatsV2CallAt = now(); // считаем попытку состоявшейся
                $this->saveSyncError($account, $e);
                $this->warn("[{$index}/{$total}] ✗ {$account->label}: {$e->getMessage()}");
            }
        }

        $this->info("Готово. Синхронизировано: {$processed}, ошибок: {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    protected function saveSyncError(AvitoAccount $account, \Throwable $e): void
    {
        $stats = $account->stats_data ?? [];
        $stats['error'] = $e->getMessage();
        $stats['synced_at'] = now()->toDateTimeString();
        $account->stats_data = $stats;
        $account->save();
    }
}
