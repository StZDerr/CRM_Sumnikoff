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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();

            // Привязка к организации — обязательна (без FK, чтобы не зависеть от порядка миграций)
            $table->unsignedBigInteger('organization_id');

            // Раздельное ФИО (обязательные поля)
            $table->string('first_name');
            $table->string('middle_name');
            $table->string('last_name')->nullable();

            $table->string('position')->nullable();

            $table->string('phone');
            $table->string('email')->nullable();

            // Предпочитаемый мессенджер и контакт в нём
            $table->enum('preferred_messenger', ['telegram', 'whatsapp', 'viber', 'skype', 'call', 'other'])->nullable();
            $table->string('messenger_contact')->nullable();

            $table->text('comment')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // Индексы
            $table->index('phone');
            $table->index('email');
            $table->index('organization_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
