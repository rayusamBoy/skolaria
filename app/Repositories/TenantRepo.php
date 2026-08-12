<?php

namespace App\Repositories;

use App\Models\Tenant;
use App\Models\TenantDomain as Domain;
use App\Models\TenantManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

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
     * @return Tenant|Builder
     */
    public function whereTenantQuery(array $where): Tenant|Builder
    {
        return Tenant::where($where);
    }

    /**
     * Build a query to find a tenant.
     * @param array $where
     * @return mixed|Tenant
     */
    public function whereInQuery(string $column, array $where): Tenant|Builder
    {
        return Tenant::whereIn($column, $where);
    }

    /**
     * Restore a soft deleted tenant.
     * @param int $id
     * @return void
     */
    public function restoreTenant(int $id): void
    {
        Tenant::withTrashed()->where('id', $id)->restore();
    }

    /**
     * Permanently delete a tenant.
     * @param int $id
     * @return void
     */
    public function forceDeleteTenant(int $id): void
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
     * @return Collection
     */
    public function getTrashedTenants(): Collection
    {
        return Tenant::onlyTrashed()->get();
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

    /*********** Manager ***************/

    /**
     * Insert manager data 
     * @param array $data
     */
    public function insertManagerData(array $data)
    {
        return TenantManager::insert($data);
    }

    /**
     * Build a query to find a tenant manager.
     * @param array $where
     * @return TenantManager|Builder
     */
    public function whereTenantManagerQuery(array $where): TenantManager|Builder
    {
        return TenantManager::where($where);
    }
}
