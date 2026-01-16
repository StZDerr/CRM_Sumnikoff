<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasFactory, SoftDeletes;

    /** Поля массового заполнения */
    protected $fillable = [
        'entity_type',
        'name_full',
        'name_short',
        'phone',
        'email',
        'inn',
        'kpp',
        'ogrnip',
        'legal_address',
        'actual_address',
        'account_number',
        'bank_name',
        'corr_account',
        'bic',
        'notes',
        'campaign_status_id',
        'campaign_source_id',
        'created_by',
        'updated_by',
    ];

    /** Приведения типов */
    protected $casts = [
        'campaign_status_id' => 'integer',
        'campaign_source_id' => 'integer',
    ];

    /** Связи */

    // Сотрудники / контакты организации
    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    // Текущий статус (campaign_statuses)
    public function status()
    {
        return $this->belongsTo(CampaignStatus::class, 'campaign_status_id');
    }

    // Текущий источник (campaign_sources)
    public function source()
    {
        return $this->belongsTo(CampaignSource::class, 'campaign_source_id');
    }

    // Все статусы/источники, привязанные к организации
    public function campaignStatuses()
    {
        return $this->hasMany(CampaignStatus::class);
    }

    public function campaignSources()
    {
        return $this->hasMany(CampaignSource::class);
    }

    /** Хелперы */

    // Установить текущий статус
    public function setStatus(?CampaignStatus $status)
    {
        $this->campaign_status_id = $status?->id;
        $this->save();

        return $this;
    }

    // Установить текущий источник
    public function setSource(?CampaignSource $source)
    {
        $this->campaign_source_id = $source?->id;
        $this->save();

        return $this;
    }

    public function projects()
    {
        return $this->hasMany(\App\Models\Project::class);
    }

    public function __toString()
    {
        return $this->name_short ?: $this->name_full;
    }

    /**
     * Scope: упорядочить организации по name_short или name_full
     */
    public function scopeOrdered($query)
    {
        // COALESCE возвращает первое ненулевое значение
        return $query->orderByRaw('COALESCE(name_short, name_full) ASC');
    }

    /**
     * Удобное отображение для списка: $organization->title
     */
    public function getTitleAttribute(): ?string
    {
        return $this->name_short ?: $this->name_full;
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }
}
