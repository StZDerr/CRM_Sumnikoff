<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryReportProjectBonus extends Model
{
    protected $fillable = [
        'salary_report_id',
        'project_id',
        'contract_amount',
        'bonus_percent',
        'max_bonus',
        'days_worked',
        'bonus_amount',
    ];

    protected $casts = [
        'contract_amount' => 'decimal:2',
        'bonus_percent' => 'decimal:2',
        'max_bonus' => 'decimal:2',
        'days_worked' => 'decimal:2',
        'bonus_amount' => 'decimal:2',
    ];

    public function salaryReport()
    {
        return $this->belongsTo(SalaryReport::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
