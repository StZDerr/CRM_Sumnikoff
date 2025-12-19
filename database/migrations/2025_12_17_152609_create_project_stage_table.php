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
        Schema::create('project_stage', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('stage_id')->constrained('stages')->cascadeOnDelete();

            // Порядок этапов в проекте (опционально)
            $table->unsignedInteger('sort_order')->default(0);

            // Доп. поля, например: дата завершения этапа
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->unique(['project_id', 'stage_id']);
            $table->index(['project_id']);
            $table->index(['stage_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_stage');
    }
};
