<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('project_lawyer_comment_files', function (Blueprint $table) {
            $table->id();
            // Используем unsignedBigInteger + индекс вместо foreign key, т.к. таблица project_lawyer_comments
            // может быть создана в другой миграции с более поздним timestamp.
            $table->unsignedBigInteger('project_lawyer_comment_id')->index();
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('project_lawyer_comment_files');
    }
};
