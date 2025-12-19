<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Photo extends Model
{
    use HasFactory;

    protected $fillable = [
        'path',
        'original_name',
        'order',
        'project_comment_id',
    ];

    public function comment()
    {
        return $this->belongsTo(ProjectComment::class, 'project_comment_id');
    }

    // Удобный атрибут для получения публичного URL
    public function getUrlAttribute()
    {
        return Storage::url($this->path);
    }
}
