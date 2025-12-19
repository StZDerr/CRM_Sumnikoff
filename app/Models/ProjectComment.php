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
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function photos()
    {
        return $this->hasMany(\App\Models\Photo::class, 'project_comment_id')->orderBy('order');
    }
}
