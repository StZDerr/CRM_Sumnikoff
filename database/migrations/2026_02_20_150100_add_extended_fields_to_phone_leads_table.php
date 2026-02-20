<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('phone_leads', function (Blueprint $table) {
            $table->string('region')->nullable()->after('name');
            $table->foreignId('lead_topic_id')->nullable()->after('region')->constrained('lead_topics')->nullOnDelete();
            $table->text('comment')->nullable()->after('note');
            $table->dateTime('deadline_at')->nullable()->after('comment');
            $table->decimal('amount', 12, 2)->nullable()->after('deadline_at');
            $table->date('deal_start_date')->nullable()->after('amount');
            $table->foreignId('campaign_source_id')->nullable()->after('deal_start_date')->constrained('campaign_sources')->nullOnDelete();
            $table->foreignId('responsible_user_id')->nullable()->after('campaign_source_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('phone_leads', function (Blueprint $table) {
            $table->dropConstrainedForeignId('responsible_user_id');
            $table->dropConstrainedForeignId('campaign_source_id');
            $table->dropColumn('deal_start_date');
            $table->dropColumn('amount');
            $table->dropColumn('deadline_at');
            $table->dropColumn('comment');
            $table->dropConstrainedForeignId('lead_topic_id');
            $table->dropColumn('region');
        });
    }
};
