<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkTimeEdit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'editable_id',
        'editable_type',
        'old_started_at',
        'old_ended_at',
        'new_started_at',
        'new_ended_at',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'old_started_at' => 'datetime',
            'old_ended_at' => 'datetime',
            'new_started_at' => 'datetime',
            'new_ended_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function editable()
    {
        return $this->morphTo();
    }
}
