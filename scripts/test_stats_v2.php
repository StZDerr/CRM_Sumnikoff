<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AvitoAccount;
use Illuminate\Support\Facades\Http;

$account = AvitoAccount::where('is_active', true)->first();

if (! $account) {
    echo "Нет активных аккаунтов\n";
    exit(1);
}

echo "Account: {$account->label}\n";

$oauth = $account->oauth_data ?? [];
$token = data_get($oauth, 'access_token');
$profile = $account->profile_data ?? [];
$userId = (int) data_get($profile, 'id');

echo "User ID: {$userId}\n";
echo "Token: ".substr($token, 0, 20)."...\n\n";

$today = now()->format('Y-m-d');
$dateFrom = now()->subDays(6)->format('Y-m-d');

echo "=== Stats V2 Request ===\n";
echo "URL: https://api.avito.ru/stats/v2/accounts/{$userId}/items\n";
echo "dateFrom: {$dateFrom}, dateTo: {$today}\n\n";

try {
    $response = Http::withToken($token)
        ->acceptJson()
        ->timeout(60)
        ->post("https://api.avito.ru/stats/v2/accounts/{$userId}/items", [
            'dateFrom' => $dateFrom,
            'dateTo' => $today,
            'grouping' => 'day',
            'metrics' => ['presenceSpending', 'promoSpending', 'spending'],
            'limit' => 1000,
            'offset' => 0,
        ]);

    echo "HTTP Status: {$response->status()}\n\n";
    echo "=== Raw Response ===\n";
    echo json_encode($response->json(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)."\n\n";

    // Парсим как в сервисе
    $payload = $response->json() ?? [];
    $groupings = (array) data_get($payload, 'result.groupings', []);
    $todayTs = (int) \Illuminate\Support\Carbon::today()->startOfDay()->timestamp;

    echo "=== Parsed ===\n";
    echo "Today timestamp: {$todayTs}\n\n";

    foreach ($groupings as $entry) {
        $ts = (int) data_get($entry, 'id', 0);
        $date = date('Y-m-d', $ts);
        $metrics = (array) data_get($entry, 'metrics', []);
        $map = [];
        foreach ($metrics as $m) {
            $map[data_get($m, 'slug')] = data_get($m, 'value');
        }
        $spending = ($map['spending'] ?? 0) / 100;
        $presence = ($map['presenceSpending'] ?? 0) / 100;
        $promo = ($map['promoSpending'] ?? 0) / 100;
        $isToday = $ts === $todayTs ? ' <<<< TODAY' : '';
        echo "{$date} (ts={$ts}): spending={$spending}₽, presence={$presence}₽, promo={$promo}₽{$isToday}\n";
    }

} catch (\Throwable $e) {
    echo "ERROR: ".$e->getMessage()."\n";
}
