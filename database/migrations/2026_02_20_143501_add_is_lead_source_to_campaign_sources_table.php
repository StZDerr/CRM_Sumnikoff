<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * - добавляем boolean `is_lead_source` (default false)
     * - отмечаем все текущие записи как источники для лидов
     */
    public function up(): void
    {
        // Защита на случай, если столбец уже существует (чтобы миграция была идемпотентной)
        if (! Schema::hasColumn('campaign_sources', 'is_lead_source')) {
            Schema::table('campaign_sources', function (Blueprint $table) {
                $table->boolean('is_lead_source')->default(false)->after('sort_order');
            });
        }

        // Не помечаем автоматически существующие записи — оставляем `false`.
        // Флаг ставится только при явном действии пользователя.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('campaign_sources', 'is_lead_source')) {
            Schema::table('campaign_sources', function (Blueprint $table) {
                $table->dropColumn('is_lead_source');
            });
        }
    }
};
