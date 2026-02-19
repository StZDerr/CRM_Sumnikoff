<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('avito_accounts', function (Blueprint $table) {
            $table->text('notification_settings')->nullable()->after('stats_data');
            $table->text('notification_state')->nullable()->after('notification_settings');
        });
    }

    public function down(): void
    {
        Schema::table('avito_accounts', function (Blueprint $table) {
            $table->dropColumn(['notification_settings', 'notification_state']);
        });
    }
};
