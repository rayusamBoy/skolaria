<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Stancl\Tenancy\Contracts\Tenant;
use Stancl\Tenancy\Database\Models\Domain as BaseDomain;
use Stancl\Tenancy\Tenancy;

class TenantDomain extends BaseDomain
{
    use LogsActivity;

    protected $table = 'domains';

    /**
     * @return BelongsTo<Tenant&Model, $this>
     */
    public function tenant(): BelongsTo
    {
        /** @var class-string<Tenant&Model> $tenantModel */
        $tenantModel = config('tenancy.models.tenant');

        /** @var BelongsTo<Model&Tenant, $this> */
        return $this->belongsTo($tenantModel, Tenancy::tenantKeyColumn())->withTrashed(); // Tenant can be soft deleted
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->dontSubmitEmptyLogs()
            ->logOnlyDirty();
    }
}
