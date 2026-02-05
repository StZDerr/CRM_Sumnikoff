<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskDeadlineChange extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'changed_by',
        'old_deadline_at',
        'new_deadline_at',
        'reason',
    ];

    protected $casts = [
        'old_deadline_at' => 'datetime',
        'new_deadline_at' => 'datetime',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
