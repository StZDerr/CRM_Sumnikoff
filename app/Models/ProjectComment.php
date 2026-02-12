<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'body',
        'month',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        // Показывать пользователя даже если он soft-deleted — чтобы имя оставалось видимым
        return $this->belongsTo(\App\Models\User::class)->withTrashed();
    }

    public function photos()
    {
        return $this->hasMany(\App\Models\Photo::class, 'project_comment_id')->orderBy('order');
    }
}
