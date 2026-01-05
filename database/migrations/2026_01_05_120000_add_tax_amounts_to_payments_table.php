<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'vat_amount')) {
                $table->decimal('vat_amount', 15, 2)->default(0)->after('amount');
            }
            if (!Schema::hasColumn('payments', 'usn_amount')) {
                $table->decimal('usn_amount', 15, 2)->default(0)->after('vat_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'vat_amount')) {
                $table->dropColumn('vat_amount');
            }
            if (Schema::hasColumn('payments', 'usn_amount')) {
                $table->dropColumn('usn_amount');
            }
        });
    }
};
