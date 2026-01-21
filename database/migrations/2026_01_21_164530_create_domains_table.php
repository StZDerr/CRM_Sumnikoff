<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('provider'); // reg_ru, manual, other
            $table->string('provider_service_id')->nullable();
            $table->string('name')->unique();
            $table->string('status');
            $table->date('expires_at')->nullable();
            $table->decimal('renew_price', 10, 2)->nullable();
            $table->string('currency', 10)->nullable();
            $table->boolean('auto_renew')->default(false);
            $table->timestamps();

            $table->unique(['provider', 'provider_service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
};
