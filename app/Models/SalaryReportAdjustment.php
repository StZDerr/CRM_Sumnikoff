<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryReportAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'salary_report_id',
        'type',
        'amount',
        'comment',
        'sort_order',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function salaryReport()
    {
        return $this->belongsTo(SalaryReport::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }
}
