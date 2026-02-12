<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Project;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;

$arg = $argv[1] ?? null;
if (! $arg) {
    echo "Usage: php scripts/simulate_payment_allocation.php <project id or title>\n";
    exit(1);
}

if (is_numeric($arg)) {
    $project = Project::find((int)$arg);
} else {
    $project = Project::where('title', $arg)->first();
}

if (! $project) {
    echo "Project not found: {$arg}\n";
    exit(2);
}

$today = Carbon::create(2026, 2, 11)->startOfDay(); // keep same date for reproducibility
$contractAmount = (float)$project->contract_amount;
$start = Carbon::make($project->contract_date)->startOfDay();

// build periods forward until either payments exhausted or N months
$periods = [];
$periodStart = $start->copy();
for ($i=0; $i<12; $i++) {
    $nextStart = $periodStart->copy()->addMonthNoOverflow();
    if ($nextStart->day < $periodStart->day) {
        $periodEnd = $nextStart->endOfDay();
    } else {
        $periodEnd = $nextStart->subSecond();
    }

    $periods[] = [
        'start' => $periodStart->copy(),
        'end' => $periodEnd->copy(),
        'expected' => 0.0,
        'invoices' => [],
        'covered_amount' => 0.0,
    ];

    $periodStart = $periodEnd->copy()->addSecond();
}

// load invoices up to last period end
$lastEnd = end($periods)['end']->copy()->endOfDay();
$invoices = Invoice::where('project_id', $project->id)
    ->where(function($q) use ($lastEnd){
        $q->whereNotNull('issued_at')->whereDate('issued_at', '<=', $lastEnd->toDateString())
          ->orWhere(function($q2) use ($lastEnd){
              $q2->whereNull('issued_at')->whereDate('created_at', '<=', $lastEnd->toDateString());
          });
    })->get(['id','amount','issued_at','created_at']);

foreach ($invoices as $inv) {
    $d = $inv->issued_at ? Carbon::make($inv->issued_at) : Carbon::make($inv->created_at);
    foreach ($periods as &$p) {
        if ($d->between($p['start'], $p['end'])) {
            $p['invoices'][] = ['id'=>$inv->id, 'date'=>$d->toDateString(), 'amount'=>(float)$inv->amount];
            $p['expected'] += (float)$inv->amount;
            break;
        }
    }
}
unset($p);

// For periods that have no invoices, expected is contract amount if period end < today
foreach ($periods as &$p) {
    if ($p['expected'] <= 0 && $p['end']->lt($today)) {
        $p['expected'] = $contractAmount;
    }
}
unset($p);

// Sum payments chronologically and allocate to earliest periods
$payments = Payment::where('project_id', $project->id)->orderBy('payment_date')->get();
$paymentBalance = (float)$payments->sum('amount');

$remaining = $paymentBalance;
for ($i=0; $i<count($periods); $i++) {
    if ($remaining <= 0) break;
    $need = $periods[$i]['expected'] - $periods[$i]['covered_amount'];
    if ($need <= 0) continue;
    $alloc = min($need, $remaining);
    $periods[$i]['covered_amount'] += $alloc;
    $remaining -= $alloc;
}

// Anything remaining is prepaid for future months
$prepaid = $remaining;

// Output
echo "Project: {$project->id} - {$project->title}\n";
echo "Contract amount: " . number_format($contractAmount,2,'.',' ') . "\n";
echo "Payments total: " . number_format($paymentBalance,2,'.',' ') . "\n\n";

foreach ($periods as $idx => $p) {
    $num = $idx+1;
    $label = $p['start']->format('Y-m-d') . " — " . $p['end']->format('Y-m-d');
    $exp = number_format($p['expected'],2,'.',' ');
    $cov = number_format($p['covered_amount'],2,'.',' ');
    echo sprintf("Period %2d | %s | expected=%8s | covered=%8s\n", $num, $label, $exp, $cov);
}

echo "\nPrepaid remaining after allocation: " . number_format($prepaid,2,'.',' ') . "\n";

exit(0);
