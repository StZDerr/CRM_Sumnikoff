<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_time_edits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id') // кто изменил
                ->constrained()
                ->cascadeOnDelete();

            $table->morphs('editable');
            // editable_id
            // editable_type
            // (WorkSession или WorkBreak)

            $table->timestamp('old_started_at')->nullable();
            $table->timestamp('old_ended_at')->nullable();

            $table->timestamp('new_started_at')->nullable();
            $table->timestamp('new_ended_at')->nullable();

            $table->text('comment'); // ОБЯЗАТЕЛЬНЫЙ комментарий

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_time_edits');
    }
};
