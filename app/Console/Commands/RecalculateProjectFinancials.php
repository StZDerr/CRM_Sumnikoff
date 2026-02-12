<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class RecalculateProjectFinancials extends Command
{
    protected $signature = 'projects:recalc-financials {--project=} {--chunk=200} {--dry-run}';

    protected $description = 'Recalculate debt, received_total and balance for projects as of end of previous month';

    public function handle(): int
    {
        $projectId = $this->option('project');
        $chunkSize = (int) ($this->option('chunk') ?: 200);
        $dryRun = (bool) $this->option('dry-run');

        $query = Project::query();

        if (! empty($projectId)) {
            $query->where('id', $projectId);
        }

        $updated = 0;
        $asOf = Carbon::now()->startOfMonth()->subSecond();

        $query->chunkById($chunkSize, function ($projects) use (&$updated, $asOf, $dryRun) {
            foreach ($projects as $project) {
                $receivedTotal = (float) Payment::where('project_id', $project->id)
                    ->whereRaw('DATE(COALESCE(payment_date, payments.created_at)) <= ?', [$asOf->toDateString()])
                    ->sum('amount');

                $debt = $this->calculateExpectedDebt($project, $asOf);
                $balance = $debt - $receivedTotal;

                if ($dryRun) {
                    $this->line(sprintf(
                        'as-of=%s Project #%d: debt=%.2f, received_total=%.2f, balance=%.2f',
                        $asOf->toDateString(),
                        $project->id,
                        $debt,
                        $receivedTotal,
                        $balance
                    ));

                    continue;
                }

                $project->updateQuietly([
                    'debt' => $debt,
                    'debt_calculated_at' => $asOf,
                    'received_total' => $receivedTotal,
                    'received_calculated_at' => $asOf,
                    'balance' => $balance,
                    'balance_calculated_at' => $asOf,
                ]);

                $updated++;
            }
        });

        if ($dryRun) {
            $this->info('Dry run completed. No changes were saved.');
        } else {
            $this->info("Recalculation completed as of {$asOf->toDateString()} (end of previous month). Updated projects: {$updated}.");
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

        // Подгружаем все счета проекта до конца периода (включая те, что выписаны до даты договора)
        // Это позволяет учитывать преддоговорные счета в первом периоде
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

        // Начисление происходит в сам день периода (contract day), а не после полного завершения месяца.
        // Периоды считаются от даты договора с month overflow (31 -> 28/29) через addMonthNoOverflow.
        while ($periodStart->lte($end) && $guard < 240) {
            $nextStart = $periodStart->copy()->addMonthNoOverflow();
            $periodEnd = $nextStart->copy()->subSecond();

            if ($periodEnd->gt($end)) {
                $periodEnd = $end->copy();
            }

            // Sum invoices that fall into this period.
            // For the first period we also include invoices issued before contract date
            // (they are treated as belonging to the first period).
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

            // Каждый начавшийся период даёт либо сумму счетов в периоде, либо — полную сумму контракта
            // но полную сумму контракта добавляем только если период полностью прошёл (не частичный текущий период).
            if ($sumInvoices > 0) {
                $expectedSum += $sumInvoices;
            } else {
                if ($periodEnd->lte($today)) {
                    $expectedSum += $contractAmount;
                }
            }

            $periodStart = $nextStart;
            $guard++;
        }

        return round($expectedSum, 2);
    }
}
