<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'organization_id',
        'city',
        'marketer_id',
        'importance_id',
        'contract_amount',
        'contract_date',
        'payment_method_id',
        'payment_due_day',
        'debt',
        'comment',
        'received_total',
        'received_calculated_at',
        'balance',
        'balance_calculated_at',
        'debt',
        'closed_at',
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
}
