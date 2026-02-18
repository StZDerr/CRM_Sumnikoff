<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvitoAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by',
        'label',
        'project_id',
        'oauth_data',
        'profile_data',
        'stats_data',
        'last_synced_at',
        'is_active',
    ];

    protected $casts = [
        'oauth_data' => 'encrypted:array',
        'profile_data' => 'encrypted:array',
        'stats_data' => 'encrypted:array',
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function project()
    {
        return $this->belongsTo(\App\Models\Project::class, 'project_id');
    }
}
