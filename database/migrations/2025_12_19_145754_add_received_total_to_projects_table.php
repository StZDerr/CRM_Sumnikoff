<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->decimal('received_total', 15, 2)->default(0)->after('debt')->comment('Сумма поступлений по проекту');
            $table->timestamp('received_calculated_at')->nullable()->after('received_total');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['received_total', 'received_calculated_at']);
        });
    }
};
