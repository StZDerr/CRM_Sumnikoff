<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProjectLawyerCommentFile extends Model
{
    use HasFactory;

    protected $table = 'project_lawyer_comment_files';

    protected $fillable = [
        'project_lawyer_comment_id',
        'path',
        'original_name',
    ];

    public function comment()
    {
        return $this->belongsTo(ProjectLawyerComment::class, 'project_lawyer_comment_id');
    }

    public function getUrlAttribute()
    {
        return Storage::url($this->path);
    }
}
