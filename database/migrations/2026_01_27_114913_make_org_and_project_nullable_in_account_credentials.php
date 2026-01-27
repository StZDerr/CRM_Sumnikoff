<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('account_credentials', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->change();
            $table->foreignId('project_id')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('account_credentials', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable(false)->change();
            $table->foreignId('project_id')->nullable(false)->change();
        });
    }
};
