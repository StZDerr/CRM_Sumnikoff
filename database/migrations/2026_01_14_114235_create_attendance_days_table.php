<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Таблица статусов
        Schema::create('attendance_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique(); // work, remote, short, absent
            $table->string('title', 50);          // человекочитаемое название
            $table->string('color', 7)->nullable(); // цвет для табеля (например #86efac)
            $table->timestamps();
        });

        // Вставляем сразу 4 базовых статуса
        DB::table('attendance_statuses')->insert([
            ['code' => 'work',   'title' => 'Работал',    'color' => '#86efac', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'remote', 'title' => 'Удаленно',   'color' => '#93c5fd', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'short',  'title' => 'Сокращённый', 'color' => '#fde68a', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'absent', 'title' => 'Отсутствовал', 'color' => '#f87171', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Таблица учёта посещаемости
        Schema::create('attendance_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('status_id')->constrained('attendance_statuses');
            $table->date('date');
            $table->string('comment')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'date']); // нельзя отметить один день дважды
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_days');
        Schema::dropIfExists('attendance_statuses');
    }
};
