<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Project;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;

$project = Project::find(1); // Трактор Тула

echo "=== Project: {$project->title} ===\n";
echo "contract_date: {$project->contract_date}\n";
echo "contract_amount: {$project->contract_amount}\n";
echo "closed_at: {$project->closed_at}\n\n";

$contractStart = Carbon::make($project->contract_date)->startOfMonth();
$contractAmount = (float) $project->contract_amount;
$closedAt = $project->closed_at ? Carbon::make($project->closed_at)->endOfMonth() : null;
$currentMonth = Carbon::now()->startOfMonth();

echo "contractStart: {$contractStart}\n";
echo "closedAt: {$closedAt}\n";
echo "currentMonth: {$currentMonth}\n\n";

// Invoices
$invoicesMap = [];
$invoiceRows = Invoice::selectRaw("project_id, DATE_FORMAT(COALESCE(issued_at, created_at), '%Y-%m') as ym, SUM(amount) as total")
    ->where('project_id', $project->id)
    ->groupBy('project_id', 'ym')
    ->get();
foreach ($invoiceRows as $r) {
    $invoicesMap[$r->project_id][$r->ym] = (float) $r->total;
}
echo "Invoices: " . json_encode($invoicesMap[$project->id] ?? []) . "\n";

// Payments
$paymentsMap = [];
$paymentRows = Payment::selectRaw("project_id, DATE_FORMAT(COALESCE(payment_date, created_at), '%Y-%m') as ym, SUM(amount) as total")
    ->where('project_id', $project->id)
    ->groupBy('project_id', 'ym')
    ->get();
foreach ($paymentRows as $r) {
    $paymentsMap[$r->project_id][$r->ym] = (float) $r->total;
}
echo "Payments: " . json_encode($paymentsMap[$project->id] ?? []) . "\n\n";

// Определяем самую раннюю дату для расчёта
$calcStart = $contractStart->copy();
$projectInvoices = $invoicesMap[$project->id] ?? [];
$projectPayments = $paymentsMap[$project->id] ?? [];
$allMonths = array_unique(array_merge(array_keys($projectInvoices), array_keys($projectPayments)));

foreach ($allMonths as $ym) {
    $monthDate = Carbon::createFromFormat('Y-m', $ym)->startOfMonth();
    if ($monthDate->lt($calcStart)) {
        $calcStart = $monthDate->copy();
    }
}
echo "calcStart (adjusted): {$calcStart}\n\n";

// Calculate balance
$tempBalance = 0;
$tempCur = $calcStart->copy();

echo "=== Balance Calculation ===\n";
while ($tempCur->lte($currentMonth)) {
    $key = $tempCur->format('Y-m');
    $invoiced = (float) ($invoicesMap[$project->id][$key] ?? 0);
    $paid = (float) ($paymentsMap[$project->id][$key] ?? 0);
    
    $expected = $invoiced > 0 ? $invoiced : ($tempCur->gte($contractStart) ? $contractAmount : 0);
    $tempBalance = $tempBalance + $paid - $expected;
    
    echo "{$key}: invoiced={$invoiced}, paid={$paid}, expected={$expected}, balance={$tempBalance}\n";
    $tempCur->addMonthNoOverflow();
}

echo "\n=== Future Extension ===\n";
echo "tempBalance after current month: {$tempBalance}\n";

if ($tempBalance > 0) {
    $futureBalance = $tempBalance;
    $futureCur = $currentMonth->copy()->addMonthNoOverflow();
    $maxFutureMonths = 24;
    $count = 0;
    
    while ($count < $maxFutureMonths) {
        echo "Adding {$futureCur->format('Y-m')}: futureBalance before={$futureBalance}\n";
        
        $futureBalance -= $contractAmount;
        echo "  futureBalance after deduction: {$futureBalance}\n";
        $futureCur->addMonthNoOverflow();
        $count++;
        
        if ($futureBalance <= 0) {
            echo "  Stopping: balance is now <= 0\n";
            break;
        }
    }
    echo "Extended {$count} months\n";
} else {
    echo "No overpayment to extend\n";
}
