<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Сумма задолженности (положительная — проект должен нам)
            $table->decimal('debt', 15, 2)->default(0)->after('contract_amount')->comment('Долг проекта (тотальный период)');
            // Когда в последний раз пересчитывали долг
            $table->timestamp('debt_calculated_at')->nullable()->after('debt');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['debt', 'debt_calculated_at']);
        });
    }
};
