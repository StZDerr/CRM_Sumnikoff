<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_marketer_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // маркетолог
            $table->timestamp('assigned_at');      // когда назначен
            $table->timestamp('unassigned_at')->nullable(); // когда снят (null = текущий)
            $table->string('reason')->nullable();  // причина: 'transfer', 'vacation', 'fired'
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['project_id', 'assigned_at']);
            $table->index(['user_id', 'assigned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_marketer_history');
    }
};
