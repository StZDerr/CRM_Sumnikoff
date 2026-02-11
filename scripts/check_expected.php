<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Project;
use App\Models\Payment;
use Carbon\Carbon;

$start = Carbon::create(2026, 2, 1)->startOfDay();
$end   = Carbon::create(2026, 2, 28)->endOfDay();

$projects = Project::expectedProfitForMonth($start)
    ->where('status', Project::STATUS_IN_PROGRESS)
    ->where('balance', '>=', 0)
    ->where('contract_amount', '>', 0)
    ->select(['id', 'title', 'contract_amount', 'balance', 'status'])
    ->orderBy('title')
    ->get();

$total = 0;
foreach ($projects as $p) {
    $ei = (float) $p->balance + (float) $p->contract_amount;
    $total += $ei;
    echo sprintf(
        "#%-3d | %-35s | contract=%10s | balance=%10s | expected=%10s\n",
        $p->id,
        mb_substr($p->title, 0, 35),
        number_format($p->contract_amount, 0, '.', ' '),
        number_format($p->balance, 0, '.', ' '),
        number_format($ei, 0, '.', ' ')
    );
}

echo "\n";
echo "Projects count: " . $projects->count() . "\n";
echo "TOTAL expected:  " . number_format($total, 0, '.', ' ') . "\n";

$received = (float) Payment::whereIn('project_id', $projects->pluck('id')->all() ?: [0])
    ->whereRaw('DATE(COALESCE(payment_date, payments.created_at)) between ? and ?', [
        $start->toDateString(), $end->toDateString()
    ])
    ->sum('payments.amount');

echo "Received month:  " . number_format($received, 0, '.', ' ') . "\n";
echo "Remaining:       " . number_format($total - $received, 0, '.', ' ') . "\n";
