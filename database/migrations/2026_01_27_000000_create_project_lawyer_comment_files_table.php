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
            $table->foreignId('project_lawyer_comment_id')->constrained('project_lawyer_comments')->onDelete('cascade');
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