<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Specialty extends Model
{
    // Заполняемые поля
    protected $fillable = ['name', 'salary', 'active'];

    // Приведение типов
    protected $casts = [
        'salary' => 'integer',
        'active' => 'boolean',
    ];

    // Связь: специальность → пользователи
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
