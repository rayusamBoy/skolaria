<?php

namespace App\Repositories;

use App\Models\Tenant;
use App\Models\TenantDomain as Domain;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class TenantRepo
{
    /*********** Tenant ***************/

    /**
     * Get all tenants with their domain.
     */
    public function getAllTenants($with_domain = true): Collection
    {
        return $with_domain ? Tenant::with('domain')->get() : Tenant::all();
    }

    /**
     * Create a new tenant.
     */
    public function createTenant(array $data): Tenant
    {
        return Tenant::create($data);
    }

    /**
     * Find a tenant by ID, including its domain.
     */
    public function findOrFailTenant(string $id): ?Tenant
    {
        return Tenant::with('domain')->findOrFail($id);
    }

    /**
     * Build a query to find a tenant.
     */
    public function whereTenantQuery(array $where): Tenant|Builder
    {
        return Tenant::where($where);
    }

    /**
     * Build a query to find a tenant.
     *
     * @return mixed|Tenant
     */
    public function whereInQuery(string $column, array $where): Tenant|Builder
    {
        return Tenant::whereIn($column, $where);
    }

    /**
     * Restore a soft deleted tenant.
     */
    public function restoreTenant(string $id): void
    {
        Tenant::withTrashed()->where('id', $id)->restore();
    }

    /**
     * Permanently delete a tenant.
     */
    public function forceDeleteTenant(string $id): void
    {
        // Delete tenant record + trigger Stancl job pipeline
        $tenant = Tenant::withTrashed()->findOrFail($id);
        $tenant->forceDelete();
    }

    /**
     * Permanently delete all soft deleted tenants.
     */
    public function forceDeleteOnlyTrashed()
    {
        Tenant::onlyTrashed()->forceDelete();
    }

    /**
     * Get all soft deleted tenants.
     */
    public function getTrashedTenants(): Collection
    {
        return Tenant::onlyTrashed()->get();
    }

    /*********** Domain ***************/

    /**
     * Update an existing domain or create a new one for a tenant.
     *
     * @param  array  $attributes  Conditions to find the domain
     * @param  array  $values  Values to set/update
     */
    public function updateOrCreateDomain(array $attributes, array $values): Domain
    {
        return Domain::updateOrCreate($attributes, $values);
    }
}
