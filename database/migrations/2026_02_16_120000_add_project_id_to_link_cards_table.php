<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('link_cards', function (Blueprint $table) {
            if (! Schema::hasColumn('link_cards', 'project_id')) {
                $table->foreignId('project_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('projects')
                    ->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('link_cards', function (Blueprint $table) {
            if (Schema::hasColumn('link_cards', 'project_id')) {
                $table->dropConstrainedForeignId('project_id');
            }
        });
    }
};
