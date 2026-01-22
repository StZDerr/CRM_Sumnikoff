<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_expense_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monthly_expense_id')->constrained('monthly_expenses')->cascadeOnDelete();
            $table->string('month', 7); // YYYY-MM
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('expense_id')->nullable()->constrained('expenses')->nullOnDelete();
            $table->timestamps();

            $table->unique(['monthly_expense_id', 'month']);
            $table->index('month');
            $table->index('expense_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_expense_statuses');
    }
};
