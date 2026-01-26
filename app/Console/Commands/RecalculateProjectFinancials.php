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

        $start = Carbon::make($project->contract_date)->startOfMonth();
        $end = $today->copy()->endOfMonth();

        if (! empty($project->closed_at)) {
            $closed = Carbon::make($project->closed_at)->endOfMonth();
            if ($closed->lt($end)) {
                $end = $closed;
            }
        }

        // Если контракт начинается позже конца периода — нет ожидаемых платежей
        if ($start->gt($end)) {
            return 0.0;
        }

        // Суммы выставленных счетов по месяцам для проекта (формат: YYYY-MM => total)
        $invoicesByMonth = Invoice::selectRaw("DATE_FORMAT(COALESCE(issued_at, created_at), '%Y-%m') as ym, SUM(amount) as total")
            ->where('project_id', $project->id)
            ->where(function ($q) use ($start, $end) {
                $q->whereNotNull('issued_at')->whereBetween('issued_at', [$start->toDateString(), $end->toDateString()])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->whereNull('issued_at')->whereBetween('created_at', [$start->toDateString(), $end->toDateString()]);
                    });
            })
            ->groupBy('ym')
            ->pluck('total', 'ym')
            ->toArray();

        $expectedSum = 0.0;
        $periodStart = $start->copy();
        $guard = 0;

        while ($periodStart->lte($end) && $guard < 240) {
            $ym = $periodStart->format('Y-m');

            // Если есть счёт в месяце — используем его сумму, иначе — contract_amount
            $expectedSum += isset($invoicesByMonth[$ym]) && (float) $invoicesByMonth[$ym] > 0
                ? (float) $invoicesByMonth[$ym]
                : $contractAmount;

            $periodStart->addMonthNoOverflow();
            $guard++;
        }

        return round($expectedSum, 2);
    }
}
