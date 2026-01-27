<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectLawyerComment extends Model
{
    use HasFactory;

    protected $table = 'project_lawyer_comments';

    protected $fillable = [
        'project_id',
        'user_id',
        'comment',
        'file_path', // backward compatibility for old comments
    ];

    // Связь с проектом
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Автор комментария
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Новые файлы (несколько) для комментария юриста
    public function files()
    {
        return $this->hasMany(ProjectLawyerCommentFile::class, 'project_lawyer_comment_id');
    }
}
