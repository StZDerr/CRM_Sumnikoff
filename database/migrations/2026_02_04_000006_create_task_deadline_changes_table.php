<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_deadline_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('changed_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('old_deadline_at')->nullable();
            $table->timestamp('new_deadline_at')->nullable();
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->index(['task_id']);
            $table->index(['changed_by']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_deadline_changes');
    }
};
