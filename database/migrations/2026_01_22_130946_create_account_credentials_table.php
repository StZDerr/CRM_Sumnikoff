<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountCredentialsTable extends Migration
{
    public function up()
    {
        Schema::create('account_credentials', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');

            $table->string('type'); // 'client', 'website_user', 'database', 'ftp'
            $table->string('name'); // название аккаунта/сервиса
            $table->string('login')->nullable();
            $table->string('password')->nullable();
            $table->string('db_name')->nullable(); // имя БД (для type = database)
            $table->text('notes')->nullable(); // любые дополнительные данные

            $table->enum('status', ['active', 'stop_list'])->default('active');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'project_id', 'status']);
            $table->index(['type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('account_credentials');
    }
}
