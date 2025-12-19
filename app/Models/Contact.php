<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use HasFactory, SoftDeletes;

    /** Поля массового заполнения */
    protected $fillable = [
        'organization_id',
        'first_name',
        'middle_name',
        'last_name',
        'position',
        'phone',
        'email',
        'preferred_messenger',
        'messenger_contact',
        'comment',
    ];

    /** Приведения типов */
    protected $casts = [
        'organization_id' => 'integer',
    ];

    /** Связи */
    public function organization()
    {
        return $this->belongsTo(\App\Models\Organization::class, 'organization_id');
    }

    /** Возвращает ФИО (читабельное) */
    public function getFullNameAttribute(): string
    {
        return trim(implode(' ', array_filter([
            $this->last_name,
            $this->first_name,
            $this->middle_name,
        ])));
    }

    /** Scope: поиск по имени/телефону/email */
    public function scopeSearch($query, ?string $q)
    {
        if (empty($q)) {
            return $query;
        }

        return $query->where(function ($qb) use ($q) {
            $qb->where('first_name', 'like', "%{$q}%")
                ->orWhere('middle_name', 'like', "%{$q}%")
                ->orWhere('last_name', 'like', "%{$q}%")
                ->orWhere('phone', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%");
        });
    }

    public function __toString()
    {
        return $this->full_name ?: ($this->phone ?? $this->email ?? (string) $this->id);
    }
}
