<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class AccountCredential extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'updated_by',
        'organization_id',
        'project_id',
        'type',        // новый тип аккаунта
        'name',
        'login',
        'password',
        'db_name',     // новое поле для имени БД
        'notes',
        'status',
    ];

    /**
     * Автоматически шифруем пароль при сохранении
     */
    public function setPasswordAttribute($value)
    {
        if ($value !== null) {
            $this->attributes['password'] = Crypt::encryptString($value);
        }
    }

    /**
     * Автоматически расшифровываем пароль при получении
     */
    public function getPasswordAttribute($value)
    {
        if ($value !== null) {
            return Crypt::decryptString($value);
        }

        return null;
    }

    /**
     * Связь с пользователем, кто создал
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Связь с пользователем, кто последний обновил
     */
    public function updatedByUser()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Связь с организацией
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Связь с проектом
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Скоуп для фильтрации по типу аккаунта
     */
    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Логи доступа (просмотры/копирования/раскрытия пароля)
     */
    public function logs()
    {
        return $this->hasMany(\App\Models\AccountCredentialLog::class);
    }
}

