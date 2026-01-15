<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ProjectMarketerHistory extends Model
{
    protected $table = 'project_marketer_history';

    protected $fillable = [
        'project_id',
        'user_id',
        'assigned_at',
        'unassigned_at',
        'reason',
        'assigned_by',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'unassigned_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function marketer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Количество дней работы над проектом в этом назначении
     * Учитывает дату закрытия проекта
     */
    public function getDaysWorkedAttribute(): int
    {
        $end = $this->unassigned_at ?? now();

        // Если проект закрыт — не считаем дни после закрытия
        if ($this->project && $this->project->closed_at) {
            $end = $end->min($this->project->closed_at);
        }

        return $this->assigned_at->lte($end) ? $this->assigned_at->diffInDays($end) : 0;
    }

    /**
     * Количество дней в указанном периоде
     * Учитывает дату закрытия проекта
     */
    public function daysInPeriod(Carbon $from, Carbon $to): int
    {
        $start = $this->assigned_at->max($from);
        $end = ($this->unassigned_at ?? now())->min($to);

        // Если проект закрыт — не считаем дни после закрытия
        if ($this->project && $this->project->closed_at) {
            $end = $end->min($this->project->closed_at);
        }

        return $start->lte($end) ? $start->diffInDays($end) + 1 : 0;
    }
}
