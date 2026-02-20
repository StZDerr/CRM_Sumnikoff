<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadTopic extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function (self $model) {
            if ((int) $model->sort_order <= 0) {
                $max = static::max('sort_order');
                $model->sort_order = $max ? $max + 1 : 1;
            }
        });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
