<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_report_project_bonuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salary_report_id')->constrained('salary_reports')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->decimal('contract_amount', 12, 2)->default(0); // сумма контракта на момент расчёта
            $table->decimal('bonus_percent', 5, 2)->default(0);    // процент премии
            $table->decimal('max_bonus', 12, 2)->default(0);       // максимальная премия за проект
            $table->decimal('days_worked', 5, 2)->default(0);      // отработано дней (с учётом коэффициентов)
            $table->decimal('bonus_amount', 12, 2)->default(0);    // итоговая премия за проект
            $table->timestamps();

            $table->unique(['salary_report_id', 'project_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_report_project_bonuses');
    }
};
