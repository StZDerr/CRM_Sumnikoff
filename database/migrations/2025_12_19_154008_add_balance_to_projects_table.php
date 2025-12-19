<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->decimal('balance', 15, 2)->default(0)->after('received_total')->comment('Разница: debt - received_total (положительная — проект должен)');
            $table->timestamp('balance_calculated_at')->nullable()->after('balance');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['balance', 'balance_calculated_at']);
        });
    }
};
