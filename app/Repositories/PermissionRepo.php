<?php

namespace App\Repositories;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionRepo
{
    /*********** Permission ***************/
    public function getPermissions()
    {
        return Permission::orderBy('name')->get();
    }

    public function create(array $attributes)
    {
        return Permission::create($attributes);
    }

    public function get(array $where)
    {
        return Permission::where($where)->first();
    }

    public function update(array $where, array $attributes)
    {
        return Permission::where($where)->update($attributes);
    }

    public function delete(array $where)
    {
        return Permission::where($where)->delete();
    }

    /*********** Role ***************/
    public function getRoles()
    {
        return Role::with('permissions')->orderBy('name')->get();
    }

    public function createRole(array $attributes)
    {
        return Role::create($attributes);
    }

    public function getRole(array $where)
    {
        return Role::where($where)->with('permissions')->first();
    }

    public function updateRole(array $where, array $attributes)
    {
        return Role::where($where)->update($attributes);
    }

    public function deleteRole(array $where)
    {
        return Role::where($where)->delete();
    }
}
