<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceDay extends Model
{
    protected $fillable = [
        'user_id',
        'status_id',
        'date',
        'comment',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    // Связь с пользователем
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Связь со статусом
    public function status()
    {
        return $this->belongsTo(AttendanceStatus::class, 'status_id');
    }

    // Удобный метод для получения цвета статуса
    public function statusColor(): ?string
    {
        return $this->status?->color;
    }

    // Удобный метод для получения человекочитаемого названия статуса
    public function statusTitle(): ?string
    {
        return $this->status?->title;
    }
}
