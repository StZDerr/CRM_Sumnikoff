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
    echo "Usage: php scripts/check_project_financials.php <project id or title>\n";
    exit(1);
}

$project = null;
if (is_numeric($arg)) {
    $project = Project::find((int)$arg);
} else {
    $project = Project::where('title', $arg)->first();
}

if (! $project) {
    echo "Project not found: {$arg}\n";
    exit(2);
}

$today = Carbon::now();

// replicate calculateExpectedDebt from console command
function calculateExpectedDebtForProject(Project $project, Carbon $today): float
{
    $contractAmount = (float) ($project->contract_amount ?? 0);
    if ($contractAmount <= 0 || empty($project->contract_date)) {
        return 0.0;
    }

    if (in_array($project->payment_type, [Project::PAYMENT_TYPE_BARTER, Project::PAYMENT_TYPE_OWN], true)) {
        return 0.0;
    }

    $start = Carbon::make($project->contract_date)->startOfDay();
    $end = $today->copy()->endOfDay();

    if (! empty($project->closed_at)) {
        $closed = Carbon::make($project->closed_at)->endOfDay();
        if ($closed->lt($end)) {
            $end = $closed;
        }
    }

    if ($start->gt($end)) {
        return 0.0;
    }

    $invoices = Invoice::where('project_id', $project->id)
        ->where(function ($q) use ($end) {
            $q->whereNotNull('issued_at')->whereDate('issued_at', '<=', $end->toDateString())
                ->orWhere(function ($q2) use ($end) {
                    $q2->whereNull('issued_at')->whereDate('created_at', '<=', $end->toDateString());
                });
        })
        ->get(['amount', 'issued_at', 'created_at'])
        ->map(function ($inv) {
            $d = $inv->issued_at ? Carbon::make($inv->issued_at) : Carbon::make($inv->created_at);
            return ['date' => $d, 'amount' => (float) $inv->amount];
        });

    $expectedSum = 0.0;
    $periodStart = $start->copy();
    $guard = 0;

    while ($periodStart->lte($end) && $guard < 240) {
        $nextStart = $periodStart->copy()->addMonthNoOverflow();

        if ($nextStart->day < $periodStart->day) {
            $periodEnd = $nextStart->endOfDay();
        } else {
            $periodEnd = $nextStart->subSecond();
        }

        if ($periodEnd->gt($end)) {
            $periodEnd = $end->copy();
        }

        $sumInvoices = 0.0;
        if ($periodStart->eq($start)) {
            foreach ($invoices as $inv) {
                if ($inv['date']->lte($periodEnd)) {
                    $sumInvoices += $inv['amount'];
                }
            }
        } else {
            foreach ($invoices as $inv) {
                if ($inv['date']->gte($periodStart) && $inv['date']->lte($periodEnd)) {
                    $sumInvoices += $inv['amount'];
                }
            }
        }

        if ($sumInvoices > 0) {
            $expectedSum += $sumInvoices;
        } else {
            if ($periodEnd->lte($today)) {
                $expectedSum += $contractAmount;
            }
        }

        $periodStart = $periodEnd->copy()->addSecond();
        $guard++;
    }

    return round($expectedSum, 2);
}

$expectedDebt = calculateExpectedDebtForProject($project, $today);
$receivedTotal = (float) $project->payments()->sum('amount');
$balance = round($expectedDebt - $receivedTotal, 2);

echo "Project: {$project->id} - {$project->title}\n";
echo "Contract amount: " . number_format($project->contract_amount, 2, '.', ' ') . "\n";
echo "Contract date: " . ($project->contract_date ?: 'n/a') . "\n";
echo "Today: " . $today->toDateString() . "\n";
echo "Expected debt (calc): " . number_format($expectedDebt, 2, '.', ' ') . "\n";
echo "Received total (payments sum): " . number_format($receivedTotal, 2, '.', ' ') . "\n";
echo "Balance (debt - received): " . number_format($balance, 2, '.', ' ') . "\n\n";

$invoices = Invoice::where('project_id', $project->id)->orderBy('issued_at')->get();
if ($invoices->count()) {
    echo "Invoices:\n";
    foreach ($invoices as $inv) {
        $d = $inv->issued_at ?: $inv->created_at;
        echo sprintf(" %s | amount=%10s | issued_at=%s | created_at=%s\n",
            str_pad($inv->id, 6, ' ', STR_PAD_LEFT),
            number_format($inv->amount, 2, '.', ' '),
            $inv->issued_at,
            $inv->created_at
        );
    }
}

$payments = Payment::where('project_id', $project->id)->orderBy('payment_date')->get();
if ($payments->count()) {
    echo "\nPayments:\n";
    foreach ($payments as $p) {
        $d = $p->payment_date ?: $p->created_at;
        echo sprintf(" %s | amount=%10s | date=%s | note=%s\n",
            str_pad($p->id, 6, ' ', STR_PAD_LEFT),
            number_format($p->amount, 2, '.', ' '),
            $d,
            substr($p->note ?? '', 0, 60)
        );
    }
}

exit(0);
