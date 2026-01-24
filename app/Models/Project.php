<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    public const PAYMENT_TYPE_PAID = 'paid';

    public const PAYMENT_TYPE_BARTER = 'barter';

    public const PAYMENT_TYPE_OWN = 'own';

    public const STATUS_IN_PROGRESS = 'in_progress'; // в работе

    public const STATUS_PAUSED = 'paused';      // пауза

    public const STATUS_STOPPED = 'stopped';     // стоп

    protected $fillable = [
        'title',
        'organization_id',
        'city',
        'marketer_id',
        'importance_id',
        'contract_amount',
        'contract_date',
        'payment_method_id',
        'payment_type',
        'payment_due_day',
        'debt',
        'comment',
        'received_total',
        'received_calculated_at',
        'balance',
        'balance_calculated_at',
        'debt',
        'status',
        'closed_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'contract_amount' => 'decimal:2',
        'payment_due_day' => 'integer',
        'importance_id' => 'integer',
        'debt' => 'decimal:2',
        'balance' => 'decimal:2',
        'contract_date' => 'datetime',
        'debt_calculated_at' => 'datetime',
        'received_total' => 'decimal:2',
        'received_calculated_at' => 'datetime',
        'balance_calculated_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    /**
     * Scope: ожидаемая прибыль на текущий месяц
     */
    public function scopeExpectedProfitForMonth($query, ?Carbon $month = null)
    {
        $month = $month ?: Carbon::now();
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        // Выбираем проекты, которые активны в этот месяц (т.е. не закрыты в этом месяце).
        // Включаем проекты без даты закрытия (еще открыты) и те, что закрыты ПОЗЖЕ than end of month.
        // Исключаем бартерные и "свои" проекты (payment_type = 'barter'|'own').
        // Исключаем проекты со статусами paused и stopped
        return $query->where(function ($q) use ($end) {
            $q->whereNull('closed_at')
                ->orWhere('closed_at', '>', $end);
        })->where(function ($q) {
            $q->whereNull('payment_type')
                ->orWhereNotIn('payment_type', ['barter', 'own']);
        })->whereNotIn('status', [self::STATUS_PAUSED, self::STATUS_STOPPED]);
    }

    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function isPaused(): bool
    {
        return $this->status === self::STATUS_PAUSED;
    }

    public function isStopped(): bool
    {
        return $this->status === self::STATUS_STOPPED;
    }

    /**
     * Получить сумму ожидаемой прибыли
     */
    public static function getExpectedProfitForMonth(?Carbon $month = null): float
    {
        return self::expectedProfitForMonth($month)->sum('contract_amount'); // или 'profit', если есть поле
    }

    // Organization
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function importance()
    {
        return $this->belongsTo(\App\Models\Importance::class, 'importance_id');
    }

    // Маркетолог (User)
    public function marketer()
    {
        return $this->belongsTo(\App\Models\User::class, 'marketer_id');
    }

    // Способ оплаты
    public function paymentMethod()
    {
        return $this->belongsTo(\App\Models\PaymentMethod::class);
    }

    // Этапы: many-to-many через project_stage, с pivot полями
    public function stages()
    {
        return $this->belongsToMany(
            \App\Models\Stage::class,
            'project_stage'
        )->withPivot('sort_order', 'completed_at')
            ->withTimestamps();
    }

    public function comments()
    {
        return $this->hasMany(\App\Models\ProjectComment::class)->orderBy('created_at', 'desc');
    }

    public function vacationAssignments()
    {
        return $this->hasMany(\App\Models\VacationProject::class);
    }

    // Счета проекта
    public function invoices()
    {
        return $this->hasMany(\App\Models\Invoice::class);
    }

    // Платежи проекта
    public function payments()
    {
        return $this->hasMany(\App\Models\Payment::class);
    }

    /**
     * Рассчитать баланс: платежи - счета
     * > 0 = переплата, < 0 = долг, = 0 = оплачено
     */
    public function getCalculatedBalanceAttribute(): float
    {
        $invoicesTotal = $this->invoices()->sum('amount');
        $paymentsTotal = $this->payments()->sum('amount');

        return (float) ($paymentsTotal - $invoicesTotal);
    }

    /**
     * Сумма выставленных счетов
     */
    public function getInvoicesTotalAttribute(): float
    {
        return (float) $this->invoices()->sum('amount');
    }

    /**
     * Сумма поступлений
     */
    public function getPaymentsTotalAttribute(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // История маркетологов на проекте
    public function marketerHistory()
    {
        return $this->hasMany(ProjectMarketerHistory::class)->orderBy('assigned_at', 'desc');
    }

    /**
     * Текущий маркетолог (из истории)
     */
    public function currentMarketerFromHistory()
    {
        return $this->hasOne(ProjectMarketerHistory::class)
            ->whereNull('unassigned_at')
            ->latestOfMany('assigned_at');
    }

    /**
     * Назначить нового маркетолога (с записью в историю)
     */
    public function assignMarketer(int $newMarketerId, ?string $reason = 'transfer'): void
    {
        // Закрываем предыдущее назначение
        $this->marketerHistory()
            ->whereNull('unassigned_at')
            ->update(['unassigned_at' => now(), 'reason' => $reason]);

        // Создаём новое назначение
        ProjectMarketerHistory::create([
            'project_id' => $this->id,
            'user_id' => $newMarketerId,
            'assigned_at' => now(),
            'assigned_by' => auth()->id(),
        ]);

        // Обновляем текущего маркетолога в проекте
        $this->update(['marketer_id' => $newMarketerId]);
    }

    /**
     * Helpers for payment type
     */
    public function isBarter(): bool
    {
        return $this->payment_type === self::PAYMENT_TYPE_BARTER;
    }

    public function isPaid(): bool
    {
        return $this->payment_type === self::PAYMENT_TYPE_PAID;
    }

    public function isOwn(): bool
    {
        return $this->payment_type === self::PAYMENT_TYPE_OWN;
    }

    /** Scopes */
    public function scopeBarter($query)
    {
        return $query->where('payment_type', self::PAYMENT_TYPE_BARTER);
    }

    public function scopePaid($query)
    {
        return $query->where('payment_type', self::PAYMENT_TYPE_PAID);
    }

    public function scopeOwn($query)
    {
        return $query->where('payment_type', self::PAYMENT_TYPE_OWN);
    }

    /**
     * Получить статистику дней по маркетологам за период
     */
    public function getMarketerDaysStats(Carbon $from, Carbon $to): array
    {
        return $this->marketerHistory()
            ->with('marketer:id,name')
            ->get()
            ->groupBy('user_id')
            ->map(fn ($records, $userId) => [
                'user_id' => $userId,
                'name' => $records->first()->marketer->name ?? '—',
                'total_days' => $records->sum(fn ($r) => $r->daysInPeriod($from, $to)),
            ])
            ->values()
            ->toArray();
    }

    public function getReportDateAttribute(): ?Carbon
    {
        if (! $this->contract_date) {
            return null;
        }

        $contractDay = $this->contract_date->day;

        return now()
            ->addMonth()
            ->setDay($contractDay);
    }
}
