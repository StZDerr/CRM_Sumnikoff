<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskDispute extends Model
{
    use HasFactory;

    public const STATUS_OPEN = 'open';
    public const STATUS_APPROVED_CLOSE = 'approved_close';
    public const STATUS_REJECTED_CLOSE = 'rejected_close';

    protected $fillable = [
        'task_id',
        'opened_by',
        'status',
        'reason',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function openedBy()
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
