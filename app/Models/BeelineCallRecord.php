<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BeelineCallRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'beeline_id',
        'beeline_id_int',
        'external_id',
        'call_id',
        'phone',
        'direction',
        'call_date',
        'duration_ms',
        'file_size',
        'comment',
        'abonent_user_id',
        'abonent_phone',
        'abonent_first_name',
        'abonent_last_name',
        'abonent_email',
        'abonent_contact_email',
        'abonent_department',
        'abonent_extension',
        'raw_payload',
        'record_file_path',
        'record_file_mime',
        'record_file_local_size',
        'record_file_sha1',
        'record_file_downloaded_at',
        'record_file_error',
        'synced_at',
    ];

    protected $casts = [
        'call_date' => 'datetime',
        'raw_payload' => 'array',
        'record_file_downloaded_at' => 'datetime',
        'synced_at' => 'datetime',
    ];
}
