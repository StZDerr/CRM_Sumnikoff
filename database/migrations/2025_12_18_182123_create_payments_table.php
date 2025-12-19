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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // 1. Проект (обязательное поле)
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();

            // 2. Сумма оплаты (обязательное)
            $table->decimal('amount', 15, 2);

            // 3. Дата оплаты (datetime, nullable)
            $table->timestamp('payment_date')->nullable();

            // 4. Способ оплаты (nullable FK -> payment_methods)
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->nullOnDelete();

            // 5. Оплаченный счёт (nullable FK -> invoices)
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();

            // Доп. поля
            $table->string('transaction_id')->nullable();
            $table->text('note')->nullable();

            $table->timestamps();

            // индексы для быстрых выборок
            $table->index('payment_date', 'payments_payment_date_index');
            $table->index('project_id', 'payments_project_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
