<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_lawyer_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete(); // проект можно удалить
            $table->foreignId('user_id')->constrained('users');                          // автор комментария — не удаляем
            $table->text('comment');                                                     // текст комментария
            $table->string('file_path')->nullable();                                     // путь к файлу
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_lawyer_comments');
    }
};
