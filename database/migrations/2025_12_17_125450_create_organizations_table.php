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
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();

            $table->string('name_full');           // Полное название
            $table->string('name_short')->nullable(); // Сокращённое название

            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            $table->string('inn');
            $table->string('ogrnip')->nullable();

            $table->text('legal_address');
            $table->text('actual_address')->nullable();

            // Банковские реквизиты
            $table->string('account_number');
            $table->string('bank_name');
            $table->string('corr_account');
            $table->string('bic')->nullable();

            $table->text('notes')->nullable();

            // Текущий статус и источник (опционально)
            $table->foreignId('campaign_status_id')->nullable()
                ->constrained('campaign_statuses')
                ->nullOnDelete();
            $table->foreignId('campaign_source_id')->nullable()
                ->constrained('campaign_sources')
                ->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();

            // Индексы
            $table->index('inn');
            $table->index('ogrnip');
            $table->index('campaign_status_id');
            $table->index('campaign_source_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
