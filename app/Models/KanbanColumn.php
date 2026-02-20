<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KanbanColumn extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sort_order',
    ];

    public function phoneLeads()
    {
        return $this->hasMany(PhoneLead::class)->orderBy('sort_order');
    }
}
