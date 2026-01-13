<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Validation\ValidationException;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'login',
        'email',
        'password',
        'role',
        'specialty_id',
        'salary_override',
        'is_department_head',
        'individual_bonus_percent', // новое поле
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'deleted_at' => 'datetime',
            'salary_override' => 'integer',
            'is_department_head' => 'boolean',
            'specialty_id' => 'integer',
            'individual_bonus_percent' => 'integer', // новое поле
        ];
    }

    // Роли
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    // Relations
    public function specialty()
    {
        return $this->belongsTo(\App\Models\Specialty::class);
    }

    public function projects()
    {
        return $this->hasMany(\App\Models\Project::class, 'marketer_id');
    }

    public function vacations()
    {
        return $this->hasMany(\App\Models\Vacation::class);
    }

    public function activeVacation()
    {
        return $this->hasOne(\App\Models\Vacation::class)->where('active', true);
    }

    // Валидируем перед сохранением
    protected static function booted()
    {
        static::saving(function ($user) {
            if ($user->is_department_head) {
                if (is_null($user->salary_override) || $user->salary_override <= 0) {
                    throw ValidationException::withMessages([
                        'salary_override' => 'Индивидуальный оклад обязателен для начальника отдела.',
                    ]);
                }
            } else {
                if (! is_null($user->salary_override)) {
                    throw ValidationException::withMessages([
                        'salary_override' => 'Индивидуальный оклад разрешён только для начальника отдела.',
                    ]);
                }
            }

            // Проверка процента премии (0-100)
            if (! is_null($user->individual_bonus_percent) && ($user->individual_bonus_percent < 0 || $user->individual_bonus_percent > 100)) {
                throw ValidationException::withMessages([
                    'individual_bonus_percent' => 'Процент индивидуальной премии должен быть от 0 до 100.',
                ]);
            }
        });
    }

    // Фактический оклад
    public function getSalaryAttribute(): int
    {
        if ($this->is_department_head) {
            return (int) ($this->salary_override ?? 0);
        }

        $spec = $this->relationLoaded('specialty') ? $this->specialty : $this->specialty()->first();

        return $spec ? (int) $spec->salary : 0;
    }

    // Процент индивидуальной премии (по умолчанию 5%)
    public function getIndividualBonusPercentAttribute($value)
    {
        return $value ?? 5;
    }

    // статус (в отпуске с .. по .. или В работе)
    public function getStatusAttribute(): string
    {
        $vac = $this->relationLoaded('activeVacation') ? $this->activeVacation : $this->activeVacation()->first();
        if ($vac) {
            return sprintf('В отпуске с %s по %s', $vac->start_date->format('d.m.Y'), $vac->end_date->format('d.m.Y'));
        }

        return 'В работе';
    }

    // Подсчёт общей суммы контрактов по проектам
    public function getTotalContractAmountAttribute(): float
    {
        return $this->projects()->sum('contract_amount');
    }

    // Количество проектов
    public function getProjectsCountAttribute(): int
    {
        return $this->projects()->count();
    }

    public function getIndividualBonusAmountAttribute()
    {
        $total = $this->projects()->sum('contract_amount');
        $percent = $this->individual_bonus_percent ?? 5; // дефолт 5%

        return $total * ($percent / 100);
    }
}
