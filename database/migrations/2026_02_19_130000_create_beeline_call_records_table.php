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
        Schema::create('beeline_call_records', function (Blueprint $table) {
            $table->id();
            $table->string('beeline_id')->unique();
            $table->unsignedBigInteger('beeline_id_int')->nullable()->index();

            $table->string('external_id')->nullable();
            $table->string('call_id')->nullable();
            $table->string('phone')->nullable();
            $table->string('direction')->nullable()->index();
            $table->dateTime('call_date')->nullable()->index();
            $table->unsignedBigInteger('duration_ms')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->text('comment')->nullable();

            $table->string('abonent_user_id')->nullable()->index();
            $table->string('abonent_phone')->nullable();
            $table->string('abonent_first_name')->nullable();
            $table->string('abonent_last_name')->nullable();
            $table->string('abonent_email')->nullable();
            $table->string('abonent_contact_email')->nullable();
            $table->string('abonent_department')->nullable();
            $table->string('abonent_extension')->nullable();

            $table->json('raw_payload');
            $table->timestamp('synced_at')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beeline_call_records');
    }
};
