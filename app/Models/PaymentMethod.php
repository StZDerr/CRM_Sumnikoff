<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    /** Поля массового заполнения */
    protected $fillable = [
        'title',
        'slug',
        'sort_order',
    ];

    /** Приведения типов */
    protected $casts = [
        'sort_order' => 'integer',
    ];

    /** Значения по умолчанию */
    protected $attributes = [
        'sort_order' => 0,
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
