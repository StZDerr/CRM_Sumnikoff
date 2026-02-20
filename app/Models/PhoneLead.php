<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhoneLead extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone',
        'name',
        'region',
        'lead_topic_id',
        'note',
        'comment',
        'deadline_at',
        'amount',
        'deal_start_date',
        'campaign_source_id',
        'responsible_user_id',
        'kanban_column_id',
        'sort_order',
        'last_call_at',
        'calls_count',
    ];

    protected $casts = [
        'last_call_at' => 'datetime',
        'calls_count' => 'integer',
        'lead_topic_id' => 'integer',
        'deadline_at' => 'datetime',
        'amount' => 'decimal:2',
        'deal_start_date' => 'date',
        'campaign_source_id' => 'integer',
        'responsible_user_id' => 'integer',
    ];

    public function kanbanColumn()
    {
        return $this->belongsTo(KanbanColumn::class);
    }

    public function callRecords()
    {
        return $this->hasMany(BeelineCallRecord::class);
    }

    public function latestCall()
    {
        return $this->hasOne(BeelineCallRecord::class)->latestOfMany('call_date');
    }

    public function topic()
    {
        return $this->belongsTo(LeadTopic::class, 'lead_topic_id');
    }

    public function campaignSource()
    {
        return $this->belongsTo(CampaignSource::class);
    }

    public function responsibleUser()
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }
}
