<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'status_id',
        'created_by',
        'assignee_id',
        'recurring_task_id',
        'title',
        'description',
        'deadline_at',
        'recurring_occurrence_date',
        'closed_at',
        'status_locked',
        'status_locked_at',
    ];

    protected $casts = [
        'deadline_at' => 'datetime',
        'recurring_occurrence_date' => 'date',
        'closed_at' => 'datetime',
        'status_locked' => 'boolean',
        'status_locked_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function status()
    {
        return $this->belongsTo(TaskStatus::class, 'status_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function recurringTask()
    {
        return $this->belongsTo(RecurringTask::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'task_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function coExecutors()
    {
        return $this->belongsToMany(User::class, 'task_user')
            ->withPivot('role')
            ->wherePivot('role', 'co_executor');
    }

    public function observers()
    {
        return $this->belongsToMany(User::class, 'task_user')
            ->withPivot('role')
            ->wherePivot('role', 'observer');
    }

    public function comments()
    {
        return $this->hasMany(TaskComment::class)->orderBy('created_at');
    }

    public function deadlineChanges()
    {
        return $this->hasMany(TaskDeadlineChange::class)->orderBy('created_at');
    }

    public function disputes()
    {
        return $this->hasMany(TaskDispute::class)->orderBy('created_at', 'desc');
    }

    public function openDispute()
    {
        return $this->hasOne(TaskDispute::class)->where('status', TaskDispute::STATUS_OPEN);
    }

    public function scopeForProject($query, int $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeByStatusSlug($query, string $slug)
    {
        return $query->whereHas('status', function ($q) use ($slug) {
            $q->where('slug', $slug);
        });
    }

    public function scopeOverdue($query)
    {
        return $query->whereNotNull('deadline_at')
            ->where('deadline_at', '<', now())
            ->whereNull('closed_at');
    }

    public function scopeDueToday($query)
    {
        return $query->whereNotNull('deadline_at')
            ->whereDate('deadline_at', now()->toDateString())
            ->whereNull('closed_at');
    }

    public function scopeDueWeek($query)
    {
        return $query->whereNotNull('deadline_at')
            ->whereBetween('deadline_at', [now()->startOfDay(), now()->addWeek()->endOfDay()])
            ->whereNull('closed_at');
    }

    public function scopeWithoutDeadline($query)
    {
        return $query->whereNull('deadline_at')->whereNull('closed_at');
    }

    public function scopeCompleted($query)
    {
        return $query->whereNotNull('closed_at');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('created_by', $userId)
                ->orWhere('assignee_id', $userId)
                ->orWhereHas('users', function ($u) use ($userId) {
                    $u->where('users.id', $userId);
                });
        });
    }

    public function getIsCompletedAttribute(): bool
    {
        return ! is_null($this->closed_at) || ($this->relationLoaded('status') && $this->status?->slug === 'done');
    }
}
