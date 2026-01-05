<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'login',
        'email',
        'password',
        'role',
    ];

    /**
     * Helpers for roles
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'deleted_at' => 'datetime',
        ];
    }

    public function projects()
    {
        return $this->hasMany(\App\Models\Project::class, 'marketer_id');
    }

    public function vacations()
    {
        return $this->hasMany(\App\Models\Vacation::class);
    }

    public function activeVacation()
    {
        return $this->hasOne(\App\Models\Vacation::class)->where('active', true);
    }

    // статус (в отпуске с .. по .. или В работе)
    public function getStatusAttribute(): string
    {
        $vac = $this->relationLoaded('activeVacation') ? $this->activeVacation : $this->activeVacation()->first();
        if ($vac) {
            return sprintf('В отпуске с %s по %s', $vac->start_date->format('d.m.Y'), $vac->end_date->format('d.m.Y'));
        }

        return 'В работе';
    }
}
