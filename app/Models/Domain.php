<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'provider',
        'provider_service_id',
        'name',
        'status',
        'expires_at',
        'renew_price',
        'currency',
        'auto_renew',
    ];

    protected $casts = [
        'expires_at' => 'date',
        'auto_renew' => 'boolean',
        'renew_price' => 'decimal:2',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // расходы
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
