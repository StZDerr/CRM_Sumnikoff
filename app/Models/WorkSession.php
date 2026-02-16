<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_day_id',
        'started_at',
        'ended_at',
        'minutes',
        'started_ip',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'minutes' => 'integer',
        ];
    }

    public function workDay()
    {
        return $this->belongsTo(WorkDay::class);
    }

    public function edits()
    {
        return $this->morphMany(WorkTimeEdit::class, 'editable');
    }
}
