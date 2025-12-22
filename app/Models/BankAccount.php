<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankAccount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'account_number',
        'correspondent_account',
        'bik',
        'inn',
        'bank_name',
        'notes',
    ];

    /**
     * Удобное отображение для списка: $bankAccount->display_name
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->title) {
            return $this->title;
        }

        return trim(($this->bank_name ? $this->bank_name.' — ' : '').($this->account_number ?? ''));
    }
}
