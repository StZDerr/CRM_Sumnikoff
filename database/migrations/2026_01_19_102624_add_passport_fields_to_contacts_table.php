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
        Schema::table('contacts', function (Blueprint $table) {
            // Паспорт РФ (увеличенная длина для хранения зашифрованных данных)
            $table->string('passport_series', 255)->nullable()->after('messenger_contact'); // Серия
            $table->string('passport_number', 255)->nullable()->after('passport_series'); // Номер
            $table->date('passport_issued_at')->nullable()->after('passport_number'); // Дата выдачи
            $table->string('passport_issued_by', 255)->nullable()->after('passport_issued_at'); // Кем выдан
            $table->string('passport_department_code', 255)->nullable()->after('passport_issued_by'); // 000-000
            $table->string('passport_birth_place', 255)->nullable()->after('passport_department_code'); // Место рождения
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn([
                'passport_series',
                'passport_number',
                'passport_issued_at',
                'passport_issued_by',
                'passport_department_code',
                'passport_birth_place',
            ]);
        });
    }
};
