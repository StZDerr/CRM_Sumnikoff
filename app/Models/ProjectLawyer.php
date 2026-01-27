<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectLawyer extends Model
{
    use HasFactory;

    protected $table = 'project_lawyer';

    protected $fillable = [
        'project_id',
        'user_id',
        'sent_by',
        'sent_at',
        'status',
        'note',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    // Связь с проектом
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Юрист, которому отправлен проект
    public function lawyer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Админ/PM, кто отправил
    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
