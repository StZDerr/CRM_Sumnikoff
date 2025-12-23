<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'issued_at',
        'project_id',
        'contract_number',
        'amount',
        'payment_method_id',
        'attachments',
        'transaction_id',
        'invoice_status_id',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'amount' => 'decimal:2',
        'attachments' => 'array',
    ];

    /**
     * Связь: счёт принадлежит проекту
     */
    public function project()
    {
        return $this->belongsTo(\App\Models\Project::class);
    }

    /**
     * Связь: назначение платежа / способ оплаты
     */
    public function paymentMethod()
    {
        return $this->belongsTo(\App\Models\PaymentMethod::class);
    }

    /**
     * Возвращает массив публичных URL для вложений (attachments)
     */
    public function getAttachmentUrlsAttribute(): array
    {
        return collect($this->attachments ?? [])
            ->map(fn ($path) => Storage::url($path))
            ->all();
    }

    /**
     * Scope: счета конкретного проекта
     */
    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    // и после paymentMethod() добавьте:
    public function invoiceStatus()
    {
        return $this->belongsTo(\App\Models\InvoiceStatus::class);
    }
}
