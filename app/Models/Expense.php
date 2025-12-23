<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'expense_date',
        'amount',
        'expense_category_id',
        'organization_id',
        'payment_method_id',
        'bank_account_id',
        'project_id',
        'document_number',
        'status',
        'description',
        'currency',
    ];

    protected $casts = [
        'expense_date' => 'datetime',
        'amount' => 'decimal:2',
    ];

    // Связи
    public function category(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ExpenseCategory::class, 'expense_category_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Organization::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(\App\Models\PaymentMethod::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(\App\Models\BankAccount::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Project::class);
    }

    // Вложенные документы (файлы)
    public function documents(): MorphMany
    {
        return $this->morphMany(\App\Models\Document::class, 'documentable')->orderBy('sort_order');
    }
}
