<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('link_cards', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Название сайта
            $table->string('url');   // Ссылка
            $table->string('icon')->nullable(); // Путь к иконке (favicon)
            $table->integer('position')->default(0); // Порядок отображения
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // Если нужно привязать к пользователю
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('link_cards');
    }
};
