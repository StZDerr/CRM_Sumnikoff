<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class UpdateProjectDebts extends Command
{
    protected $signature = 'projects:update-debts {--project=}';

    protected $description = 'Auto-generate monthly invoices for active projects';

    public function handle()
    {
        $this->info('Starting auto-invoice generation for active projects...');

        $projectId = $this->option('project');
        $now = Carbon::now();
        $currentYm = $now->format('Y-m'); // Текущий месяц (например, 2026-01)

        if ($projectId) {
            $projects = Project::whereNotNull('contract_date')->where('id', $projectId)->get();
        } else {
            $projects = Project::whereNotNull('contract_date')->get();
        }

        $created = 0;
        $skipped = 0;

        DB::transaction(function () use ($projects, $now, $currentYm, &$created, &$skipped) {
            foreach ($projects as $project) {
                $contractAmount = (float) ($project->contract_amount ?? 0);

                // Пропускаем проекты без суммы договора
                if ($contractAmount <= 0) {
                    $this->line("Project #{$project->id} ({$project->title}): пропущен — contract_amount = 0");
                    $skipped++;

                    continue;
                }

                // Пропускаем закрытые проекты
                if (! empty($project->closed_at) && Carbon::make($project->closed_at)->lt($now)) {
                    $this->line("Project #{$project->id} ({$project->title}): пропущен — проект закрыт");
                    $skipped++;

                    continue;
                }

                // Проверяем, что дата контракта не позже текущего месяца
                $contractDate = Carbon::make($project->contract_date);
                if ($contractDate->format('Y-m') > $currentYm) {
                    $this->line("Project #{$project->id} ({$project->title}): пропущен — контракт ещё не начался");
                    $skipped++;

                    continue;
                }

                // Пропускаем бартерные проекты
                if ($project->payment_type === 'barter') {
                    $this->line("Project #{$project->id} ({$project->title}): пропущен — бартерный проект");
                    $skipped++;

                    continue;
                }

                // Пропускаем бартерные проекты
                if ($project->payment_type === 'own') {
                    $this->line("Project #{$project->id} ({$project->title}): пропущен — свой проект");
                    $skipped++;

                    continue;
                }

                // Проверяем, есть ли уже счёт на текущий месяц
                $existingInvoice = Invoice::where('project_id', $project->id)
                    ->whereRaw("DATE_FORMAT(COALESCE(issued_at, created_at), '%Y-%m') = ?", [$currentYm])
                    ->exists();

                if ($existingInvoice) {
                    $this->line("Project #{$project->id} ({$project->title}): пропущен — счёт на {$currentYm} уже существует");
                    $skipped++;

                    continue;
                }

                // Генерируем номер счёта
                $invoiceNumber = $this->generateInvoiceNumber($project, $now);

                // Создаём счёт
                Invoice::create([
                    'number' => $invoiceNumber,
                    'project_id' => $project->id,
                    'issued_at' => $now,
                    'amount' => $contractAmount,
                    'payment_method_id' => 1, // Р/с
                    'invoice_status_id' => 4, // Долг
                ]);

                $this->info("Project #{$project->id} ({$project->title}): создан счёт #{$invoiceNumber} на {$contractAmount} ₽");
                $created++;
            }
        });

        $this->info("Готово! Создано счетов: {$created}, пропущено: {$skipped}");

        return Command::SUCCESS;
    }

    /**
     * Генерирует уникальный номер счёта
     */
    protected function generateInvoiceNumber(Project $project, Carbon $date): string
    {
        // Формат: INV-{project_id}-{YYYYMM}-{порядковый номер}
        $prefix = 'INV-'.$project->id.'-'.$date->format('Ym');

        // Находим последний счёт с таким префиксом
        $lastInvoice = Invoice::where('number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->first();

        if ($lastInvoice) {
            // Извлекаем порядковый номер и увеличиваем
            $parts = explode('-', $lastInvoice->number);
            $seq = (int) end($parts) + 1;
        } else {
            $seq = 1;
        }

        return $prefix.'-'.str_pad($seq, 2, '0', STR_PAD_LEFT);
    }
}
