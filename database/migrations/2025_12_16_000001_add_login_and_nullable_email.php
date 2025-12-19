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
        Schema::table('users', function (Blueprint $table) {
            // Add login (username) column
            if (! Schema::hasColumn('users', 'login')) {
                $table->string('login')->unique()->after('name');
            }

            // Make email nullable and drop unique index if exists
            if (Schema::hasColumn('users', 'email')) {
                // drop unique index if present
                try {
                    $table->dropUnique(['email']);
                } catch (\Throwable $e) {
                    // ignore if index does not exist or cannot be dropped
                }

                // make nullable
                try {
                    $table->string('email')->nullable()->change();
                } catch (\Throwable $e) {
                    // Changing column types requires doctrine/dbal; document if it fails.
                    // You may need to require "doctrine/dbal" to allow column modification.
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'login')) {
                $table->dropUnique(['login']);
                $table->dropColumn('login');
            }

            if (Schema::hasColumn('users', 'email')) {
                try {
                    $table->string('email')->nullable(false)->change();
                } catch (\Throwable $e) {
                    // ignore
                }

                // recreate unique index if needed
                try {
                    $table->unique('email');
                } catch (\Throwable $e) {
                    // ignore
                }
            }
        });
    }
};
