<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Сбрасываем флаг `is_lead_source` у всех существующих записей —
     * чтобы старые источники НЕ считались лидами по умолчанию.
     */
    public function up(): void
    {
        DB::table('campaign_sources')->update(['is_lead_source' => false]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // В редком случае отката — восстановим прежнее значение true для всех (обратим операцию).
        DB::table('campaign_sources')->update(['is_lead_source' => true]);
    }
};
