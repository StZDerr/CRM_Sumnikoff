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
        DB::table('users')
            ->where('role', 'manager')
            ->update(['role' => 'project_manager']);

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

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->change();
        });
    }
};
