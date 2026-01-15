<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_reports', function (Blueprint $table) {
            $table->id();

            // Сотрудник, для которого табель
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Месяц табеля
            $table->date('month');

            // Оклад за месяц
            $table->decimal('base_salary', 10, 2)->default(0);

            // Данные табеля
            $table->decimal('ordinary_days', 5, 2)->default(0);
            $table->decimal('remote_days', 5, 2)->default(0);
            $table->integer('audits_count')->default(0);
            $table->decimal('custom_bonus', 10, 2)->default(0);
            $table->decimal('individual_bonus', 10, 2)->default(0);
            $table->decimal('total_salary', 10, 2)->default(0);

            // Статус согласования
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');

            // Комментарий к табелю
            $table->text('comment')->nullable();

            // Кто одобрил табель и когда
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            // Метаданные: кто создал, кто обновил, кто оставил комментарий
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('commented_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // Один табель на сотрудника за месяц
            $table->unique(['user_id', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_reports');
    }
};
