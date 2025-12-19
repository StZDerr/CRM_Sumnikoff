<?php

namespace App\Console\Commands;

use App\Models\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class UpdateProjectDebts extends Command
{
    protected $signature = 'projects:update-debts';

    protected $description = 'Recalculate project debts (expected total since contract_date)';

    public function handle()
    {
        $this->info('Starting debt recalculation (expected total of fully completed months)...');

        $projects = Project::whereNotNull('contract_date')->get();

        DB::transaction(function () use ($projects) {
            foreach ($projects as $project) {
                $contractAmount = (float) ($project->contract_amount ?? 0);

                if ($contractAmount <= 0) {
                    $project->update([
                        'debt' => 0,
                        'debt_calculated_at' => now(),
                        'received_total' => 0,
                        'received_calculated_at' => now(),
                        'balance' => 0,
                        'balance_calculated_at' => now(),
                    ]);
                    $this->line("Project #{$project->id} ({$project->title}): contract_amount missing/zero — all totals set to 0");

                    continue;
                }

                // считаем количество полностью завершённых месяцев
                $start = Carbon::make($project->contract_date);
                $now = Carbon::now();

                $monthsCount = 0;
                $cursor = $start->copy()->addMonthNoOverflow();
                while ($cursor->lte($now)) {
                    $monthsCount++;
                    $cursor->addMonthNoOverflow();
                }

                $expectedTotal = round($contractAmount * $monthsCount, 2);

                // пересчитываем фактические оплаты
                $paidTotal = \App\Models\Payment::where('project_id', $project->id)
                    ->whereNotNull('payment_date')
                    ->sum('amount');
                $paidTotal = round((float) $paidTotal, 2);

                // balance = debt - received_total
                $balance = round($expectedTotal - $paidTotal, 2);

                $project->update([
                    'debt' => $expectedTotal,
                    'debt_calculated_at' => now(),
                    'received_total' => $paidTotal,
                    'received_calculated_at' => now(),
                    'balance' => $balance,
                    'balance_calculated_at' => now(),
                ]);

                $this->line("Project #{$project->id} ({$project->title}): months={$monthsCount}, debt={$expectedTotal}, received={$paidTotal}, balance={$balance}");
            }
        });

        $this->info('Project debts updated.');

        return Command::SUCCESS;
    }
}
