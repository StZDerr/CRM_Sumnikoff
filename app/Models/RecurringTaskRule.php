<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecurringTaskRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'recurring_task_id',
        'type',
        'interval_days',
        'weekly_days',
        'time_of_day',
        'start_date',
        'monthly_rules',
    ];

    protected $casts = [
        'weekly_days' => 'array',
        'monthly_rules' => 'array',
        'start_date' => 'date',
    ];

    public function recurringTask()
    {
        return $this->belongsTo(RecurringTask::class);
    }
}
