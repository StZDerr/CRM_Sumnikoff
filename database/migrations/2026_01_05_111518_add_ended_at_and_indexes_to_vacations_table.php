<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vacations', function (Blueprint $table) {
            $table->timestamp('ended_at')->nullable()->after('active');
            $table->index(['active', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::table('vacations', function (Blueprint $table) {
            $table->dropIndex(['active', 'end_date']);
            $table->dropColumn('ended_at');
        });
    }
};
