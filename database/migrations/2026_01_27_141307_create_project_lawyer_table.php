<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_lawyer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete(); // проект можно удалить
            $table->foreignId('user_id')->constrained('users');                // юрист — не удаляем
            $table->foreignId('sent_by')->constrained('users');                // кто отправил — не удаляем
            $table->dateTime('sent_at');
            $table->enum('status', ['pending', 'processed'])->default('pending');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_lawyer');
    }
};
