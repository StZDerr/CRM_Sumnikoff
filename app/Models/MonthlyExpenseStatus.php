<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyExpenseStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'monthly_expense_id',
        'month',
        'paid_at',
        'expense_id',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    public function monthlyExpense(): BelongsTo
    {
        return $this->belongsTo(MonthlyExpense::class);
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }
}
