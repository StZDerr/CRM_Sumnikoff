<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salary_reports', function (Blueprint $table) {
            // суммы
            $table->decimal('advance_amount', 10, 2)
                ->default(0)
                ->after('total_salary')
                ->comment('Сумма выданного аванса');

            $table->decimal('remaining_amount', 10, 2)
                ->default(0)
                ->after('advance_amount')
                ->comment('Остаток к выплате');

            // кто выдал / выплатил
            $table->foreignId('advance_paid_by')
                ->nullable()
                ->after('approved_at')
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('paid_by')
                ->nullable()
                ->after('advance_paid_by')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('salary_reports', function (Blueprint $table) {
            $table->dropForeign(['advance_paid_by']);
            $table->dropForeign(['paid_by']);

            $table->dropColumn([
                'advance_amount',
                'remaining_amount',
                'advance_paid_by',
                'paid_by',
            ]);
        });
    }
};
