<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectStage extends Model
{
    use HasFactory;

    protected $table = 'project_stage';

    protected $fillable = [
        'project_id',
        'stage_id',
        'sort_order',
        'completed_at',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'completed_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function stage()
    {
        return $this->belongsTo(Stage::class);
    }
    
}
