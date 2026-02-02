<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class RecalculateProjectFinancials extends Command
{
    protected $signature = 'projects:recalc-financials {--project=} {--chunk=200} {--dry-run}';

    protected $description = 'Recalculate debt, received_total and balance for projects';

    public function handle(): int
    {
        $projectId = $this->option('project');
        $chunkSize = (int) ($this->option('chunk') ?: 200);
        $dryRun = (bool) $this->option('dry-run');

        $query = Project::query()
            ->withSum('payments', 'amount');

        if (! empty($projectId)) {
            $query->where('id', $projectId);
        }

        $updated = 0;
        $now = Carbon::now();

        $query->chunkById($chunkSize, function ($projects) use (&$updated, $now, $dryRun) {
            foreach ($projects as $project) {
                $receivedTotal = (float) ($project->payments_sum_amount ?? 0);
                $debt = $this->calculateExpectedDebt($project, $now);
                $balance = $debt - $receivedTotal;

                if ($dryRun) {
                    $this->line(sprintf(
                        'Project #%d: debt=%.2f, received_total=%.2f, balance=%.2f',
                        $project->id,
                        $debt,
                        $receivedTotal,
                        $balance
                    ));

                    continue;
                }

                $project->updateQuietly([
                    'debt' => $debt,
                    'debt_calculated_at' => $now,
                    'received_total' => $receivedTotal,
                    'received_calculated_at' => $now,
                    'balance' => $balance,
                    'balance_calculated_at' => $now,
                ]);

                $updated++;
            }
        });

        if ($dryRun) {
            $this->info('Dry run completed. No changes were saved.');
        } else {
            $this->info("Recalculation completed. Updated projects: {$updated}.");
        }

        return Command::SUCCESS;
    }

    protected function calculateExpectedDebt(Project $project, Carbon $today): float
    {
        $contractAmount = (float) ($project->contract_amount ?? 0);
        if ($contractAmount <= 0 || empty($project->contract_date)) {
            return 0.0;
        }

        if (in_array($project->payment_type, [Project::PAYMENT_TYPE_BARTER, Project::PAYMENT_TYPE_OWN], true)) {
            return 0.0;
        }

        // Start from exact contract date (not startOfMonth) and iterate monthly periods
        $start = Carbon::make($project->contract_date)->startOfDay();
        $end = $today->copy()->endOfDay();

        if (! empty($project->closed_at)) {
            $closed = Carbon::make($project->closed_at)->endOfDay();
            if ($closed->lt($end)) {
                $end = $closed;
            }
        }

        // Если контракт начинается позже конца периода — нет ожидаемых платежей
        if ($start->gt($end)) {
            return 0.0;
        }

        // Подгружаем все счета проекта внутри диапазона (issued_at или created_at)
        $invoices = Invoice::where('project_id', $project->id)
            ->where(function ($q) use ($start, $end) {
                $q->whereNotNull('issued_at')->whereBetween('issued_at', [$start->toDateString(), $end->toDateString()])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->whereNull('issued_at')->whereBetween('created_at', [$start->toDateString(), $end->toDateString()]);
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

        // Iterate periods: [periodStart, periodEnd] until end — handle month overflow (e.g., Feb shorter) correctly
        while ($periodStart->lte($end) && $guard < 240) {
            $nextStart = $periodStart->copy()->addMonthNoOverflow();

            // If next month's day is smaller (month overflow like Feb), include that last day in current period
            if ($nextStart->day < $periodStart->day) {
                $periodEnd = $nextStart->endOfDay();
            } else {
                $periodEnd = $nextStart->subSecond();
            }

            if ($periodEnd->gt($end)) {
                $periodEnd = $end->copy();
            }

            // Sum invoices that fall into this period
            $sumInvoices = 0.0;
            foreach ($invoices as $inv) {
                if ($inv['date']->gte($periodStart) && $inv['date']->lte($periodEnd)) {
                    $sumInvoices += $inv['amount'];
                }
            }

            if ($sumInvoices > 0) {
                $expectedSum += $sumInvoices;
            } else {
                // Only add full contract amount if the period fully passed (not a partial current period)
                if ($periodEnd->lte($today)) {
                    $expectedSum += $contractAmount;
                }
            }

            // Start next period immediately after current period end to avoid overlapping/extra short periods
            $periodStart = $periodEnd->copy()->addSecond();
            $guard++;
        }

        return round($expectedSum, 2);
    }
}
