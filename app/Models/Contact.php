<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

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

        // Паспорт РФ
        'passport_series',
        'passport_number',
        'passport_issued_at',
        'passport_issued_by',
        'passport_department_code',
        'passport_birth_place',
        'birth_date',

        'comment',
        'created_by',
        'updated_by',
    ];

    /** Приведения типов */
    protected $casts = [
        'organization_id' => 'integer',
        'passport_issued_at' => 'date',
        'birth_date' => 'date',
    ];

    public function getAgeAttribute(): ?int
    {
        return $this->birth_date ? $this->birth_date->age : null;
    }
    /* -----------------------------------------------------------------
     |  Шифрование паспортных данных
     | -----------------------------------------------------------------
     */

    public function setPassportSeriesAttribute($value): void
    {
        $this->attributes['passport_series'] = $value
            ? Crypt::encryptString($value)
            : null;
    }

    public function getPassportSeriesAttribute($value): ?string
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    public function setPassportNumberAttribute($value): void
    {
        $this->attributes['passport_number'] = $value
            ? Crypt::encryptString($value)
            : null;
    }

    public function getPassportNumberAttribute($value): ?string
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    /* -----------------------------------------------------------------
     |  Связи
     | -----------------------------------------------------------------
     */

    public function organization()
    {
        return $this->belongsTo(\App\Models\Organization::class, 'organization_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /* -----------------------------------------------------------------
     |  Аксессоры
     | -----------------------------------------------------------------
     */

    /** Возвращает ФИО */
    public function getFullNameAttribute(): string
    {
        return trim(implode(' ', array_filter([
            $this->last_name,
            $this->first_name,
            $this->middle_name,
        ])));
    }

    /** Серия + номер (если есть) */
    public function getPassportFullAttribute(): ?string
    {
        if (! $this->passport_series || ! $this->passport_number) {
            return null;
        }

        return $this->passport_series.' '.$this->passport_number;
    }

    /* -----------------------------------------------------------------
     |  Scopes
     | -----------------------------------------------------------------
     */

    /** Поиск по имени / телефону / email */
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
        return $this->full_name
            ?: ($this->phone ?? $this->email ?? (string) $this->id);
    }
}
