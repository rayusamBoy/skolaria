<?php

namespace App\Repositories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Collection;
use Stancl\Tenancy\Database\Models\Domain;
use Illuminate\Database\Eloquent\Builder;

class TenantRepo
{
    /*********** Tenant ***************/

    /**
     * Get all tenants with their domain.
     *
     * @return Collection
     */
    public function getAllTenants($with_domain = true): Collection
    {
        return $with_domain ? Tenant::with('domain')->get() : Tenant::all();
    }

    /**
     * Create a new tenant.
     *
     * @param array $data
     * @return Tenant
     */
    public function createTenant(array $data): Tenant
    {
        return Tenant::create($data);
    }

    /**
     * Find a tenant by ID, including its domain.
     *
     * @param int $id
     * @return Tenant|null
     */
    public function findOrFailTenant(int $id): ?Tenant
    {
        return Tenant::with('domain')->findOrFail($id);
    }

    /**
     * Build a query to find a tenant.
     * @param array $where
     * @return Tenant
     */
    public function whereTenantQuery(array $where): Builder
    {
        return Tenant::where($where);
    }

    /*********** Domain ***************/

    /**
     * Update an existing domain or create a new one for a tenant.
     *
     * @param array $attributes  Conditions to find the domain
     * @param array $values      Values to set/update
     * @return Domain
     */
    public function updateOrCreateDomain(array $attributes, array $values): Domain
    {
        return Domain::updateOrCreate($attributes, $values);
    }
}
