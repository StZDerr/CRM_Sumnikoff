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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();

            // 1. Название проекта
            $table->string('title');

            // 2. Организация — nullable, при удалении организации ставим NULL
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();

            // 3. Город
            $table->string('city')->nullable();

            // 4. Маркетолог (пользователь) — nullable, при удалении пользователя ставим NULL
            $table->foreignId('marketer_id')->nullable()->constrained('users')->nullOnDelete();

            // 5. Важность (ссылка на importances) — nullable, у одной важности может быть много проектов
            $table->foreignId('importance_id')->nullable()->constrained('importances')->nullOnDelete();

            // 6. Сумма договора
            $table->decimal('contract_amount', 14, 2)->nullable();

            // 7. Тип оплаты (payment method)
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->nullOnDelete();

            // 8. Срок оплаты — день месяца (1-31)
            $table->unsignedTinyInteger('payment_due_day')->nullable();

            // 8b. Дата заключения договора
            $table->date('contract_date')->nullable();

            // 9. Комментарий
            $table->text('comment')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // Индексы
            $table->index(['organization_id']);
            $table->index(['marketer_id']);
            $table->index(['importance_id']);
            $table->index(['payment_method_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
