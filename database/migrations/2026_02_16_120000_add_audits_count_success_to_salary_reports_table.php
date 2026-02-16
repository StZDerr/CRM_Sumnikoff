<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salary_reports', function (Blueprint $table) {
            if (! Schema::hasColumn('salary_reports', 'audits_count_success')) {
                $table->unsignedInteger('audits_count_success')
                    ->default(0)
                    ->after('audits_count')
                    ->comment('Количество успешных аудитов (оплата по 1000)');
            }
        });
    }

    public function down(): void
    {
        Schema::table('salary_reports', function (Blueprint $table) {
            if (Schema::hasColumn('salary_reports', 'audits_count_success')) {
                $table->dropColumn('audits_count_success');
            }
        });
    }
};
