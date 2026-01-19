<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

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
        'salary_recipient',
    ];

    protected $casts = [
        'expense_date' => 'datetime',
        'amount' => 'decimal:2',
    ];

    // Словарь статусов
    public const STATUSES = [
        'paid' => ['label' => 'Оплачено', 'color' => 'green'],
        'awaiting' => ['label' => 'Ожидает оплаты', 'color' => 'yellow'],
        'partial' => ['label' => 'Частично оплачено', 'color' => 'orange'],
        'stoplist' => ['label' => 'Стоп-лист', 'color' => 'red'],
    ];

    // Человекочитаемый статус
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status]['label'] ?? $this->status;
    }

    // Цвет Tailwind для статуса
    public function getStatusColorAttribute(): string
    {
        return self::STATUSES[$this->status]['color'] ?? 'gray';
    }

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

    // Получатель зарплаты (если применимо)
    public function salaryRecipient(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'salary_recipient');
    }

    // Вложенные документы (файлы)
    public function documents(): MorphMany
    {
        return $this->morphMany(\App\Models\Document::class, 'documentable')->orderBy('sort_order');
    }
}
