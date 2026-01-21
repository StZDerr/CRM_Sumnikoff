<?php

namespace App\Console\Commands;

use App\Models\Domain;
use App\Services\RegApiClient;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SyncRegDomains extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reg:sync-domains';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync domains from REG.API into local database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $username = config('regapi.username');
        $password = config('regapi.password');

        if (empty($username) || empty($password)) {
            $this->error('Не заполнены REG_API_USERNAME / REG_API_PASSWORD в .env.');

            return self::FAILURE;
        }

        try {
            $client = RegApiClient::make();
            $domains = $client->getDomains();

            $pricesData = $client->getDomainPrices();
            $currency = $pricesData['currency'] ?? 'RUR';
            $prices = $pricesData['prices'] ?? [];

            $renewPrices = [];
            foreach ($prices as $tld => $priceData) {
                if (isset($priceData['renew_price'])) {
                    $renewPrices[strtolower($tld)] = $priceData['renew_price'];
                }
            }

            $created = 0;
            $updated = 0;
            $serviceIds = [];

            foreach ($domains as $item) {
                $name = $item['dname'] ?? null;
                $serviceId = $item['service_id'] ?? null;

                if (empty($name) || empty($serviceId)) {
                    continue;
                }

                $tld = $this->extractTld($name);
                $renewPrice = $tld ? ($renewPrices[$tld] ?? $renewPrices['__idn.'.$tld] ?? null) : null;

                $serviceIds[] = (string) $serviceId;

                $domain = Domain::firstOrNew([
                    'provider' => 'reg_ru',
                    'provider_service_id' => (string) $serviceId,
                ]);

                $domain->name = $name;
                $domain->status = (string) ($item['state'] ?? 'N');
                $domain->expires_at = $item['expiration_date'] ?? null;
                $domain->renew_price = $renewPrice;
                $domain->currency = $currency;
                $domain->auto_renew = false;

                if (! $domain->exists) {
                    $domain->save();
                    $created++;
                } else {
                    if ($domain->isDirty()) {
                        $domain->save();
                        $updated++;
                    }
                }
            }

            $deleted = 0;
            if (! empty($serviceIds)) {
                $deleted = Domain::where('provider', 'reg_ru')
                    ->whereNotIn('provider_service_id', $serviceIds)
                    ->delete();
            }

            $this->info("Добавлено: {$created}; Обновлено: {$updated}; Удалено: {$deleted}");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }

    private function extractTld(string $domain): ?string
    {
        $domain = trim(Str::lower($domain));
        $parts = explode('.', $domain);
        if (count($parts) < 2) {
            return null;
        }

        return end($parts);
    }
}
