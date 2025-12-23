<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // nullable, чтобы не ломать существующие записи
            $table->foreignId('bank_account_id')->nullable()->after('payment_method_id')
                ->constrained('bank_accounts')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // dropConstrainedForeignId требует Laravel 8.49+, иначе используйте dropForeign + dropColumn
            $table->dropConstrainedForeignId('bank_account_id');
        });
    }
};
