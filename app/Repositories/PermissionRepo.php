<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionRepo
{
    /*********** Permissions ***************/

    /**
     * Get all permissions ordered by name.
     *
     * @return Collection
     */
    public function getAllPermissions(): Collection
    {
        return Permission::orderBy('name')->get();
    }

    /*********** Roles ***************/

    /**
     * Get roles optionally excluding some by name and eager loading relations.
     *
     * @param array $except  Role names to exclude
     * @param array $with    Relations to eager load
     * @return Collection
     */
    public function getAllRoles(array $except = [], array $with = ['permissions']): Collection
    {
        return Role::with($with)
            ->whereNotIn('name', $except)
            ->orderBy('name')
            ->get();
    }

    /**
     * Create a new role.
     *
     * @param array $attributes
     * @return Role
     */
    public function createRole(array $attributes): Role
    {
        return Role::create($attributes);
    }

    /**
     * Get a single role by conditions, with its permissions.
     *
     * @param array $where
     * @param array $with
     * @return Role|null
     */
    public function getRoleByConditions(array $where, array $with = ['permissions']): ?Role
    {
        return Role::where($where)->with($with)->first();
    }
}
