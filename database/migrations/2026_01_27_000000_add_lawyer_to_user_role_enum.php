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
        // Добавляем новую опцию enum 'lawyer' в поле role
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', [
                'admin',
                'project_manager',
                'marketer',
                'frontend',
                'designer',
                'lawyer',
            ])->default('frontend')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', [
                'admin',
                'project_manager',
                'marketer',
                'frontend',
                'designer',
            ])->default('frontend')->change();
        });
    }
};