<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();

            $table->string('type', 100)->default('project.info');
            $table->string('title');
            $table->text('message')->nullable();
            $table->json('data')->nullable();

            $table->string('target_role', 50)->nullable();
            $table->string('target_position', 120)->nullable();

            $table->timestamp('read_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['user_id', 'read_at']);
            $table->index(['project_id']);
            $table->index(['type']);
            $table->index(['target_role']);
            $table->index(['target_position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
