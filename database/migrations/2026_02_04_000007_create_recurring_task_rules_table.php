<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_task_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recurring_task_id')->constrained('recurring_tasks')->cascadeOnDelete();
            $table->enum('type', ['daily', 'weekly', 'monthly']);

            $table->unsignedInteger('interval_days')->nullable();
            $table->json('weekly_days')->nullable();
            $table->time('time_of_day')->nullable();
            $table->date('start_date')->nullable();

            $table->json('monthly_rules')->nullable();

            $table->timestamps();

            $table->index(['recurring_task_id']);
            $table->index(['type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_task_rules');
    }
};
