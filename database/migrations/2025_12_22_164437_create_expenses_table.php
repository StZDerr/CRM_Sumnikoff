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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();

            $table->dateTime('expense_date')->default(DB::raw('CURRENT_TIMESTAMP')); // Дата расхода
            $table->decimal('amount', 14, 2)->default(0); // Сумма

            // Связи
            $table->foreignId('expense_category_id')->nullable()->constrained('expense_categories')->nullOnDelete();
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->nullOnDelete();
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();

            // Доп. поля
            $table->string('document_number')->nullable(); // номер счета/накладной/чека
            $table->string('status')->default('awaiting'); // paid|awaiting|partial
            $table->string('currency')->default('RUB'); // опционально
            $table->text('description')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // индексы для поиска/фильтрации
            $table->index('expense_date');
            $table->index('status');
            $table->index('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
