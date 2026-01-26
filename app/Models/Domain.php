<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'provider',
        'provider_service_id',
        'name',
        'status',
        'expires_at',
        'renew_price',
        'currency',
        'auto_renew',
    ];

    protected $casts = [
        'expires_at' => 'date',
        'auto_renew' => 'boolean',
        'renew_price' => 'decimal:2',
    ];

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'A' => 'Активна',
            'N' => 'Неактивна',
            'S' => 'Приостановлена',
            default => '—',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'A' => 'bg-green-100 text-green-700',
            'N' => 'bg-gray-100 text-gray-600',
            'S' => 'bg-yellow-100 text-yellow-700',
            default => 'bg-gray-50 text-gray-400',
        };
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // расходы
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
