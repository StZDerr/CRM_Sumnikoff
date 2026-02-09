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
        Schema::table('salary_reports', function (Blueprint $table) {
            $table->enum('status', [
                'draft',
                'save',          // временное сохранение
                'submitted',
                'approved',
                'advance_paid',
                'paid',
                'rejected',
            ])->default('draft')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salary_reports', function (Blueprint $table) {
            $table->enum('status', [
                'draft',
                'submitted',
                'approved',
                'advance_paid',
                'paid',
                'rejected',
            ])->default('draft')->change();
        });
    }
};
