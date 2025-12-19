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
        Schema::create('photos', function (Blueprint $table) {
            $table->id();
            $table->string('path');          // путь к файлу
            $table->string('original_name')->nullable();
            $table->integer('order')->default(0);
            $table->foreignId('project_comment_id')
                ->constrained('project_comments')
                ->cascadeOnDelete();
            $table->timestamps();
            $table->index('created_at', 'photos_created_at_index');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photos');
    }
};
