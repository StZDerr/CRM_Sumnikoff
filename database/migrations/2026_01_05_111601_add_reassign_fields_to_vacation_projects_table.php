<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vacation_projects', function (Blueprint $table) {
            $table->timestamp('reassigned_at')->nullable()->after('reassigned_to_id');
            $table->timestamp('restored_at')->nullable()->after('reassigned_at');
            $table->foreignId('restored_by')->nullable()->constrained('users')->nullOnDelete()->after('restored_at');
            $table->text('note')->nullable()->after('restored_by');

            $table->index(['restored_at']);
        });
    }

    public function down(): void
    {
        Schema::table('vacation_projects', function (Blueprint $table) {

            // 1. Удаляем foreign key
            $table->dropForeign(['restored_by']);

            // 2. Удаляем индекс
            $table->dropIndex(['restored_at']);

            // 3. Удаляем колонки
            $table->dropColumn([
                'reassigned_at',
                'restored_at',
                'restored_by',
                'note',
            ]);
        });
    }
};
