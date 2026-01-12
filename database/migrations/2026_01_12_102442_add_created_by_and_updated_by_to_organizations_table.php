<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->foreignId('created_by')
                ->nullable()
                ->after('campaign_source_id')
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->after('created_by')
                ->constrained('users')
                ->nullOnDelete();

            $table->index('created_by', 'organizations_created_by_index');
            $table->index('updated_by', 'organizations_updated_by_index');
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropIndex('organizations_created_by_index');
            $table->dropIndex('organizations_updated_by_index');
            $table->dropColumn(['created_by', 'updated_by']);
        });
    }
};
