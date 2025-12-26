<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'amount',
        'payment_date',
        'payment_method_id',
        'invoice_id',
        'transaction_id',
        'note',
        'bank_account_id',
        'payment_category_id',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function paymentCategory()
    {
        return $this->belongsTo(\App\Models\PaymentCategory::class);
    }

    // Связи
    public function project()
    {
        return $this->belongsTo(\App\Models\Project::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(\App\Models\PaymentMethod::class);
    }

    public function invoice()
    {
        return $this->belongsTo(\App\Models\Invoice::class);
    }

    // Scope: по проекту
    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function bankAccount()
    {
        return $this->belongsTo(\App\Models\BankAccount::class);
    }
}
