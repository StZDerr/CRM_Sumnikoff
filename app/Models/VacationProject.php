<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VacationProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'vacation_id',
        'project_id',
        'original_marketer_id',
        'reassigned_to_id',
    ];

    public function vacation()
    {
        return $this->belongsTo(Vacation::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function originalMarketer()
    {
        return $this->belongsTo(User::class, 'original_marketer_id');
    }

    public function reassignedTo()
    {
        return $this->belongsTo(User::class, 'reassigned_to_id');
    }
}
