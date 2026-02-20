<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$account = App\Models\AvitoAccount::where('is_active', true)->first();
$stats = $account->stats_data ?? [];

echo "Account: {$account->label}\n\n";
echo "spending_today:          " . ($stats['spending_today'] ?? 'null') . " ₽\n";
echo "spending_presence_today: " . ($stats['spending_presence_today'] ?? 'null') . " ₽\n";
echo "spending_promo_today:    " . ($stats['spending_promo_today'] ?? 'null') . " ₽\n";
echo "spending_per_day:        " . ($stats['spending_per_day'] ?? 'null') . " ₽\n";
echo "spending_source:         " . ($stats['spending_source'] ?? 'null') . "\n";
echo "spending_period_days:    " . ($stats['spending_period_days'] ?? 'null') . "\n";
echo "\nviews_today:   " . ($stats['views_today'] ?? 0) . "\n";
echo "contacts_today: " . ($stats['contacts_today'] ?? 0) . "\n";
echo "ctr:            " . ($stats['ctr'] ?? 0) . "%\n";
