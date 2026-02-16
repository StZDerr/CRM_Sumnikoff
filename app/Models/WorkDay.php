<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkDay extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'report',
        'total_work_minutes',
        'total_break_minutes',
        'is_closed',
    ];

    protected function casts(): array
    {
        return [
            'work_date' => 'date',
            'total_work_minutes' => 'integer',
            'total_break_minutes' => 'integer',
            'is_closed' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sessions()
    {
        return $this->hasMany(WorkSession::class);
    }

    public function breaks()
    {
        return $this->hasMany(WorkBreak::class);
    }
}
