<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',            // сотрудник, которому табель
        'year',
        'month',
        'base_salary',
        'ordinary_days',
        'remote_days',
        'audits_count',
        'individual_bonus',
        'fees',           // сборы (например, на ДР)
        'penalties',       // штрафы (опоздания, нарушения и т.п.)
        'individual_bonus_amount',
        'custom_bonus',
        'total_salary',
        'status',             // статус табеля (draft, pending, approved, rejected)
        'comment',            // комментарий
        'created_by',         // кто создал
        'updated_by',         // кто обновил
        'commented_by',       // кто оставил комментарий
        'advance_amount',     // сумма выданного аванса
        'remaining_amount',   // остаток к выплате
        'advance_paid_by',    // кто выдал аванс
        'paid_by',            // кто выплатил полную зп
    ];

    // Привязка к пользователю, которому табель (включая soft-deleted)
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    // Кто создал табель (включая soft-deleted)
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    // Кто обновил табель (включая soft-deleted)
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by')->withTrashed();
    }

    // Кто оставил комментарий (включая soft-deleted)
    public function commenter()
    {
        return $this->belongsTo(User::class, 'commented_by')->withTrashed();
    }

    // Кто одобрил табель (включая soft-deleted)
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by')->withTrashed();
    }

    // Детализация премии по проектам
    public function projectBonuses()
    {
        return $this->hasMany(SalaryReportProjectBonus::class);
    }

    /**
     * Приведения типов
     */
    protected $casts = [
        'base_salary' => 'decimal:2',
        'ordinary_days' => 'decimal:2',
        'remote_days' => 'decimal:2',
        'audits_count' => 'integer',
        'individual_bonus' => 'decimal:2',
        'custom_bonus' => 'decimal:2',
        'total_salary' => 'decimal:2',
        'fees' => 'decimal:2',
        'penalties' => 'decimal:2',
    ];

    // Читабельная метка статуса (локализованная)
    public function getStatusLabelAttribute(): string
    {
        $key = 'attendance.statuses.'.$this->status;
        $translation = __($key);

        // Если перевод совпадает с ключом — возвращаем удобочитаемую форму
        if ($translation === $key) {
            return ucfirst($this->status);
        }

        return $translation;
    }
}
