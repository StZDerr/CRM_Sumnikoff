<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phone_leads', function (Blueprint $table) {
            $table->id();

            $table->string('phone')->unique()->index();
            $table->string('name')->nullable();
            $table->text('note')->nullable();

            $table->foreignId('kanban_column_id')
                ->constrained('kanban_columns')
                ->cascadeOnDelete();

            $table->unsignedInteger('sort_order')->default(0)->index();

            $table->timestamp('last_call_at')->nullable()->index();
            $table->unsignedInteger('calls_count')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phone_leads');
    }
};
