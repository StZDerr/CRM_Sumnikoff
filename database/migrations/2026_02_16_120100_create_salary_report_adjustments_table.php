<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_report_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salary_report_id')->constrained('salary_reports')->cascadeOnDelete();
            $table->enum('type', ['fee', 'penalty']);
            $table->decimal('amount', 10, 2)->default(0)->comment('Положительная сумма удержания');
            $table->string('comment', 255)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['salary_report_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_report_adjustments');
    }
};
