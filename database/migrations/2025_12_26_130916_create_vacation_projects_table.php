<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vacation_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacation_id')->constrained('vacations')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('original_marketer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reassigned_to_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['vacation_id', 'project_id']);
            $table->index(['project_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacation_projects');
    }
};
