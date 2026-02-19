<?php

namespace App\Console\Commands;

use App\Models\AvitoAccount;
use App\Services\AvitoApiService;
use App\Services\AvitoNotificationService;
use Illuminate\Console\Command;

class SyncAvitoAccounts extends Command
{
    protected $signature = 'avito:sync-accounts';

    protected $description = 'Synchronize active Avito accounts and process threshold notifications';

    public function handle(AvitoApiService $avitoApiService, AvitoNotificationService $avitoNotificationService): int
    {
        $processed = 0;
        $failed = 0;

        AvitoAccount::query()
            ->where('is_active', true)
            ->chunkById(50, function ($accounts) use (&$processed, &$failed, $avitoApiService, $avitoNotificationService) {
                foreach ($accounts as $account) {
                    try {
                        $result = $avitoApiService->syncAccount($account);

                        $account->oauth_data = $result['oauth_data'];
                        $account->profile_data = $result['profile_data'];
                        $account->stats_data = $result['stats_data'];
                        $account->last_synced_at = now();
                        $account->save();

                        $avitoNotificationService->processThresholdAlerts($account);

                        $processed++;
                    } catch (\Throwable $e) {
                        $failed++;
                        $this->saveSyncError($account, $e);
                        $this->warn("{$account->label}: {$e->getMessage()}");
                    }
                }
            });

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
