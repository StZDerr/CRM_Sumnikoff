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
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Наименование счёта (пример: "ООО Ромашка - р/с")
            $table->string('account_number')->unique(); // Р/сч
            $table->string('correspondent_account')->nullable(); // к/сч
            $table->string('bik')->nullable(); // БИК
            $table->string('inn')->nullable(); // ИНН
            $table->string('bank_name')->nullable(); // Наименование банка
            $table->text('notes')->nullable(); // Доп. примечание
            $table->timestamps();
            $table->softDeletes(); // по желанию — для "мягкого" удаления
            $table->index(['bik']);
            $table->index(['inn']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
