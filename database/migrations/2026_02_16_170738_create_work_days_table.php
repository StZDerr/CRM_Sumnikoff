<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_days', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('work_date');

            $table->text('report'); // что сделал за день (обязательно)

            $table->integer('total_work_minutes')->default(0);
            $table->integer('total_break_minutes')->default(0);

            $table->boolean('is_closed')->default(false);

            $table->timestamps();

            $table->unique(['user_id', 'work_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_days');
    }
};
