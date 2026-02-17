<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            // Должность
            $table->string('position')->nullable()->after('role');

            // Вид работы (один)
            $table->string('work_type')->nullable()->after('position');

            // Аватар (путь к файлу)
            $table->string('avatar')->nullable()->after('work_type');

        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'position',
                'work_type',
                'avatar',
            ]);
        });
    }
};
