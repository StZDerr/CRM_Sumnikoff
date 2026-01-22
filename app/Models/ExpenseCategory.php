<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpenseCategory extends Model
{
    use HasFactory, SoftDeletes;

    /** Поля массового заполнения */
    protected $fillable = [
        'title',
        'slug',
        'sort_order',
        'is_office',
        'is_salary',
        'is_domains_hosting',
    ];

    /** Приведения типов */
    protected $casts = [
        'sort_order' => 'integer',
        'is_office' => 'boolean',
        'is_salary' => 'boolean',
        'is_domains_hosting' => 'boolean',
    ];

    /** Значения по умолчанию */
    protected $attributes = [
        'sort_order' => 0,
        'is_office' => false,
        'is_salary' => false,
        'is_domains_hosting' => false,
    ];

    /** При создании устанавливаем sort_order = max + 1 (если не указан) */
    protected static function booted()
    {
        static::creating(function (self $model) {
            if ($model->sort_order === null || (int) $model->sort_order <= 0) {
                $max = static::max('sort_order');
                $model->sort_order = $max !== null ? $max + 1 : 1;
            }
        });
    }

    /** Scope: упорядочить по sort_order */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }

    /** Scope: только офисные категории */
    public function scopeOffice($query)
    {
        return $query->where('is_office', true);
    }

    /** Scope: только не офисные категории */
    public function scopeNotOffice($query)
    {
        return $query->where('is_office', false);
    }

    /** Проверка: офисная категория? */
    public function isOffice(): bool
    {
        return (bool) $this->is_office;
    }

    /**
     * Переместить элемент на указанную позицию (переупорядочивает остальные)
     *
     * @return $this
     */
    public function moveToPosition(int $position)
    {
        $position = max(1, $position);
        $old = (int) $this->sort_order;
        if ($old === $position) {
            return $this;
        }

        if ($old < $position) {
            static::where('sort_order', '>', $old)
                ->where('sort_order', '<=', $position)
                ->decrement('sort_order');
        } else {
            static::where('sort_order', '>=', $position)
                ->where('sort_order', '<', $old)
                ->increment('sort_order');
        }

        $this->sort_order = $position;
        $this->save();

        return $this;
    }

    public function __toString()
    {
        return (string) $this->title;
    }
}
