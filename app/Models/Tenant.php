<?php

namespace App\Models;

use App\Models\TenantDomain;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Stancl\Tenancy\Database\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains, SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    public static function getCustomColumns(): array
    {
        return [
            ...parent::getCustomColumns(),
            'deleted_at',
            'created_at',
            'updated_at',
        ];
    }

    public function domain()
    {
        return $this->hasOne(TenantDomain::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->dontSubmitEmptyLogs()
            ->logOnlyDirty();
    }
}
