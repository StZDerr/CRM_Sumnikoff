<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tasks')) {
            return;
        }

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('status_id')->constrained('task_statuses');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assignee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('recurring_task_id')->nullable()->constrained('recurring_tasks')->nullOnDelete();

            $table->string('title');
            $table->text('description')->nullable();

            $table->timestamp('deadline_at')->nullable();
            $table->date('recurring_occurrence_date')->nullable();

            $table->timestamp('closed_at')->nullable();
            $table->boolean('status_locked')->default(false);
            $table->timestamp('status_locked_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_id']);
            $table->index(['status_id']);
            $table->index(['assignee_id']);
            $table->index(['deadline_at']);
            $table->index(['closed_at']);
            $table->unique(['recurring_task_id', 'recurring_occurrence_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
