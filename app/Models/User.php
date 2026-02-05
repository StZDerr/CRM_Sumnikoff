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

    public const ROLE_ADMIN = 'admin';

    public const ROLE_PROJECT_MANAGER = 'project_manager';

    public const ROLE_MARKETER = 'marketer';

    public const ROLE_LAWYER = 'lawyer';

    public const ROLE_FRONTEND = 'frontend';

    public const ROLE_DESIGNER = 'designer';

    public const ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_PROJECT_MANAGER,
        self::ROLE_MARKETER,
        self::ROLE_FRONTEND,
        self::ROLE_DESIGNER,
        self::ROLE_LAWYER,
    ];

    protected $fillable = [
        'name',
        'login',
        'email',
        'birth_date',
        'password',
        'role',
        'specialty_id',
        'salary_override',
        'is_department_head',
        'forecast_amount',
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
            'forecast_amount' => 'decimal:2',
            'birth_date' => 'date',
        ];
    }

    public function isLawyer(): bool
    {
        return $this->hasRole(self::ROLE_LAWYER);
    }

    public function scopeLawyers($query)
    {
        return $query->where('role', self::ROLE_LAWYER);
    }
    // ===== Роли =====

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN);
    }

    public function isProjectManager(): bool
    {
        return $this->hasRole(self::ROLE_PROJECT_MANAGER);
    }

    public function isMarketer(): bool
    {
        return $this->hasRole(self::ROLE_MARKETER);
    }

    public function isFrontend(): bool
    {
        return $this->hasRole(self::ROLE_FRONTEND);
    }

    public function isDesigner(): bool
    {
        return $this->hasRole(self::ROLE_DESIGNER);
    }

    // ===== Scope’ы =====

    public function scopeAdmins($query)
    {
        return $query->where('role', self::ROLE_ADMIN);
    }

    public function scopeProjectManagers($query)
    {
        return $query->where('role', self::ROLE_PROJECT_MANAGER);
    }

    public function scopeMarketers($query)
    {
        return $query->where('role', self::ROLE_MARKETER);
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

    public function socials()
    {
        return $this->hasMany(UserSocial::class);
    }

    public function monthlyExpenses()
    {
        return $this->hasMany(MonthlyExpense::class);
    }

    public function tasksCreated()
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    public function tasksAssigned()
    {
        return $this->hasMany(Task::class, 'assignee_id');
    }

    public function taskParticipations()
    {
        return $this->belongsToMany(Task::class, 'task_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function taskComments()
    {
        return $this->hasMany(TaskComment::class);
    }

    public function taskDeadlineChanges()
    {
        return $this->hasMany(TaskDeadlineChange::class, 'changed_by');
    }

    public function recurringTasksCreated()
    {
        return $this->hasMany(RecurringTask::class, 'created_by');
    }

    public function recurringTasksAssigned()
    {
        return $this->hasMany(RecurringTask::class, 'assignee_id');
    }

    // Валидируем перед сохранением
    protected static function booted()
    {
        static::saving(function ($user) {
            if (! in_array($user->role, self::ROLES, true)) {
                throw ValidationException::withMessages([
                    'role' => 'Недопустимая роль пользователя.',
                ]);
            }

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

    public function attendanceDays()
    {
        return $this->hasMany(\App\Models\AttendanceDay::class);
    }

    // История назначений на проекты как маркетолог
    public function marketerProjectHistory()
    {
        return $this->hasMany(\App\Models\ProjectMarketerHistory::class, 'user_id');
    }

    /**
     * Сколько дней пользователь работал над проектами за период
     */
    public function getProjectDaysInPeriod(\Carbon\Carbon $from, \Carbon\Carbon $to): int
    {
        return $this->marketerProjectHistory()
            ->get()
            ->sum(fn ($record) => $record->daysInPeriod($from, $to));
    }
}
