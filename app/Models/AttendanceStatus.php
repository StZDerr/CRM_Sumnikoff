<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceStatus extends Model
{
    protected $fillable = [
        'code',   // 'work', 'remote', 'short', 'absent'
        'title',  // Человекочитаемое название
        'color',  // Цвет для табеля, например '#86efac'
    ];

    // Статические константы для базовых статусов
    const WORK = 'work';

    const REMOTE = 'remote';

    const SHORT = 'short';

    const ABSENT = 'absent';

    // Связь с днями посещаемости
    public function attendanceDays()
    {
        return $this->hasMany(AttendanceDay::class, 'status_id');
    }
}
