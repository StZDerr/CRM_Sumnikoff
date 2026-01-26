<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salary_reports', function (Blueprint $table) {
            if (! Schema::hasColumn('salary_reports', 'fees')) {
                $table->decimal('fees', 10, 2)->default(0)->comment('Сборы (например, на ДР)');
            }

            if (! Schema::hasColumn('salary_reports', 'penalties')) {
                $table->decimal('penalties', 10, 2)->default(0)->comment('Штрафы (опоздания, нарушения и т.п.)');
            }
        });
    }

    public function down(): void
    {
        Schema::table('salary_reports', function (Blueprint $table) {
            if (Schema::hasColumn('salary_reports', 'fees')) {
                $table->dropColumn('fees');
            }

            if (Schema::hasColumn('salary_reports', 'penalties')) {
                $table->dropColumn('penalties');
            }
        });
    }
};
