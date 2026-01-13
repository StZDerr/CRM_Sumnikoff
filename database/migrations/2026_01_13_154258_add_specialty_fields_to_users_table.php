<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // specialty_id nullable, при удалении специальности — null
            $table->foreignId('specialty_id')->nullable()->constrained('specialties')->nullOnDelete();
            // Индивидуальный оклад — unsigned integer (nullable, только для начальника)
            $table->unsignedInteger('salary_override')->nullable();
            // Флаг начальника отдела
            $table->boolean('is_department_head')->default(false);
            // % Индивидуальной премии
            $table->unsignedTinyInteger('individual_bonus_percent')->nullable()->comment('Процент индивидуальной премии, 0-100');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['specialty_id']);
            $table->dropColumn(['specialty_id', 'salary_override', 'is_department_head', 'individual_bonus_percent']);
        });
    }
};
