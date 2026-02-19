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
        Schema::table('beeline_call_records', function (Blueprint $table) {
            $table->string('record_file_path')->nullable()->after('raw_payload');
            $table->string('record_file_mime')->nullable()->after('record_file_path');
            $table->unsignedBigInteger('record_file_local_size')->nullable()->after('record_file_mime');
            $table->string('record_file_sha1', 40)->nullable()->after('record_file_local_size');
            $table->timestamp('record_file_downloaded_at')->nullable()->after('record_file_sha1');
            $table->text('record_file_error')->nullable()->after('record_file_downloaded_at');

            $table->index('record_file_downloaded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('beeline_call_records', function (Blueprint $table) {
            $table->dropIndex(['record_file_downloaded_at']);
            $table->dropColumn([
                'record_file_path',
                'record_file_mime',
                'record_file_local_size',
                'record_file_sha1',
                'record_file_downloaded_at',
                'record_file_error',
            ]);
        });
    }
};
