<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    protected $fillable = [
        'path', 'original_name', 'mime', 'size', 'sort_order',
    ];

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    // удобный accessor: $document->url
    public function getUrlAttribute(): ?string
    {
        return $this->path ? Storage::url($this->path) : null;
    }
}
