<?php

namespace App\Console\Commands;

use App\Models\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class UpdateProjectDebts extends Command
{
    protected $signature = 'projects:update-debts {--project=}';

    protected $description = 'Recalculate project debts (expected total since contract_date)';

    public function handle()
    {
        $this->info('Starting debt recalculation (expected total of fully completed months)...');

        $projectId = $this->option('project');

        if ($projectId) {
            $projects = Project::whereNotNull('contract_date')->where('id', $projectId)->get();
        } else {
            $projects = Project::whereNotNull('contract_date')->get();
        }

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

                // считаем количество полностью завершённых месяцев — учитываем закрытие проекта (closed_at)
                $start = Carbon::make($project->contract_date);

                $end = Carbon::now();
                if (! empty($project->closed_at)) {
                    $closed = Carbon::make($project->closed_at);
                    if ($closed->lt($end)) {
                        $end = $closed;
                    }
                }

                $monthsCount = 0;
                $cursor = $start->copy()->addMonthNoOverflow();
                while ($cursor->lte($end)) {
                    $monthsCount++;
                    $cursor->addMonthNoOverflow();
                }

                $expectedTotal = round($contractAmount * $monthsCount, 2);

                // пересчитываем фактические оплаты по проекту (учитываем payment_date или, если нет, created_at) без ограничений по периоду
                $paidTotal = \App\Models\Payment::where('project_id', $project->id)
                    ->where(function ($q) {
                        $q->whereNotNull('payment_date')
                            ->orWhere(function ($q2) {
                                $q2->whereNull('payment_date')->whereNotNull('created_at');
                            });
                    })
                    ->sum('amount');
                $paidTotal = round((float) $paidTotal, 2);

                // balance = received_total - debt (положительное — переплата)
                $balance = round($paidTotal - $expectedTotal, 2);

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
