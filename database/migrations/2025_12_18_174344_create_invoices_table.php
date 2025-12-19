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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            // 1. Номер счета (обязательный, уникальный)
            $table->string('number')->unique();

            // 2. Дата выставления (по умолчанию сегодня)
            $table->timestamp('issued_at')->useCurrent();

            // 4. Проект (обязательное, FK -> projects)
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();

            // 5. Номер договора (необязательный)
            $table->string('contract_number')->nullable();

            // 6. Сумма по счету (обязательное)
            $table->decimal('amount', 15, 2);

            // 7. Назначение платежа (payment_method) — nullable FK -> payment_methods
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->nullOnDelete();

            // 8. Файлы / документы (JSON массив путей) — nullable
            $table->json('attachments')->nullable()->comment('Массив путей к файлам (сканы, PDF и т.д.)');

            // 9. Номер транзакции / ID платежа
            $table->string('transaction_id')->nullable();

            $table->timestamps();

            // индекс по дате — удобно для выборок
            $table->index('issued_at', 'invoices_issued_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
