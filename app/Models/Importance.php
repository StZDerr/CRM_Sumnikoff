<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Importance extends Model
{
    /** @use HasFactory<\Database\Factories\ImportanceFactory> */
    /**
     * Поля массового заполнения
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'color',
        'sort_order',
    ];

    /**
     * Приведения типов
     *
     * @var array
     */
    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * Значения по умолчанию
     *
     * @var array
     */
    protected $attributes = [
        'sort_order' => 0,
    ];

    /**
     * Booted: установить sort_order по умолчанию (max + 1) при создании
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function (self $model) {
            if ($model->sort_order === null || (int) $model->sort_order <= 0) {
                $max = static::max('sort_order');
                $model->sort_order = $max !== null ? $max + 1 : 1;
            }
        });
    }

    /**
     * Scope для упорядоченного вывода
     */
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

    /**
     * Приведение в строку
     */
    public function __toString()
    {
        return (string) $this->name;
    }
}
