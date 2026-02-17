<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountCredentialLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_credential_id',
        'user_id',
        'action',
        'ip',
        'user_agent',
        'context',
    ];

    protected $casts = [
        'context' => 'array',
    ];

    public function credential()
    {
        return $this->belongsTo(AccountCredential::class, 'account_credential_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
