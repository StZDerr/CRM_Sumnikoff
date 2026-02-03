<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_categories', function (Blueprint $table) {
            if (! Schema::hasColumn('payment_categories', 'is_domains_hosting')) {
                $table->boolean('is_domains_hosting')->default(false)->after('sort_order');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_categories', function (Blueprint $table) {
            if (Schema::hasColumn('payment_categories', 'is_domains_hosting')) {
                $table->dropColumn('is_domains_hosting');
            }
        });
    }
};
