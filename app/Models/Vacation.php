<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vacation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'start_date',
        'end_date',
        'temp_marketer_id',
        'active',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'active' => 'boolean',
    ];

    // Отпуск принадлежит пользователю
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Временный маркетолог (на время отпуска)
    public function tempMarketer()
    {
        return $this->belongsTo(User::class, 'temp_marketer_id');
    }

    // Связанные записи проектов, затронутые отпуском
    public function projects()
    {
        return $this->hasMany(VacationProject::class);
    }

    // Scope: активные отпуска, которые пересекаются с периодом
    public function scopeActiveBetween($query, $start, $end)
    {
        return $query->where('active', true)
            ->where('start_date', '<=', $end)
            ->where('end_date', '>=', $start);
    }

    public function isActive(): bool
    {
        return (bool) $this->active;
    }
}
