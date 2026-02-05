<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('role', ['assignee', 'co_executor', 'observer']);
            $table->timestamps();

            $table->unique(['task_id', 'user_id', 'role']);
            $table->index(['task_id']);
            $table->index(['user_id']);
            $table->index(['role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_user');
    }
};
