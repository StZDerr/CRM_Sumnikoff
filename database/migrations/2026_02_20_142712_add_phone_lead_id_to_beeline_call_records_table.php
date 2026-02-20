<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('beeline_call_records', function (Blueprint $table) {
            $table->foreignId('phone_lead_id')
                ->nullable()
                ->after('id')
                ->constrained('phone_leads')
                ->nullOnDelete()
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('beeline_call_records', function (Blueprint $table) {
            $table->dropForeign(['phone_lead_id']);
            $table->dropColumn('phone_lead_id');
        });
    }
};
