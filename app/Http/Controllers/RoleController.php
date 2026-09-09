<?php

namespace App\Http\Controllers;

use App\Helpers\Usr;
use App\Repositories\PermissionRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleController extends Controller
{
    protected PermissionRepo $permission;

    public function __construct(PermissionRepo $permission)
    {
        $this->permissionMap = config('permissions.names.tenant.roles');
        parent::__construct();
        $this->permission = $permission;
    }

    /**
     * Display a listing of roles.
     */
    public function index(): View
    {
        $permissions = $this->permission->getAllPermissions();
        $data['permissions'] = $permissions;
        $data['grouped_permissions'] = $permissions->groupBy(
            fn ($perm) => explode('.', $perm->name)[0]
        );
        $data['roles'] = $this->permission->getAllRoles([], ['users', 'permissions']);

        return view('pages.roles.index', $data);
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'unique:roles,name'],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        $role_name = ucwords($validated['name']);
        $role = $this->permission->createRole(['name' => $role_name]);

        if ($request->filled('permissions')) {
            $role->syncPermissions($validated['permissions']);
        }

        return Usr::jsonStoreOk();
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit($id): View|RedirectResponse
    {
        $role = $this->permission->getRoleByConditions(['id' => $id]);

        if ($this->isProtectedRootRole($role->name)) {
            return redirect()->route('roles.index')->with('flash_warning', __('msg.denied'));
        }

        $permissions = $this->permission->getAllPermissions();
        $data['grouped_permissions'] = $permissions->groupBy(
            fn ($perm) => explode('.', $perm->name)[0]
        );
        $data['role'] = $role;

        return view('pages.roles.edit', $data);
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $role = $this->permission->getRoleByConditions(['id' => $id]);

        $validated = $request->validate([
            'name' => ['required', 'string', "unique:roles,name,{$id}"],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        // Preserve name for default roles; otherwise update with sanitized input
        $new_name = $this->isDefaultRole($role->name)
            ? $role->name
            : ucwords($validated['name']);

        $role->update(['name' => $new_name]);

        if ($request->filled('permissions')) {
            $role->syncPermissions($validated['permissions']);
        }

        return redirect()->route('roles.index')->with('flash_success', __('msg.update_ok'));
    }

    /**
     * Remove the specified role.
     */
    public function destroy($id): RedirectResponse
    {
        $role = $this->permission->getRoleByConditions(['id' => $id], ['users']);

        if ($role->users->count() > 0) {
            return back()->with('flash_warning', __('msg.role_in_use'));
        }

        if ($this->isDefaultRole($role->name)) {
            return back()->with('flash_warning', __('msg.denied'));
        }

        $role->delete();

        return redirect()->back()->with('flash_success', __('msg.del_ok'));
    }

    /**
     * Check if a role name is among the tenancy‑aware root roles.
     */
    protected function isProtectedRootRole(string $role_name): bool
    {
        return \in_array($role_name, Usr::getTenancyAwareRootRoles(), true);
    }

    /**
     * Check if a role name is among the tenancy‑aware default roles.
     */
    protected function isDefaultRole(string $role_name): bool
    {
        return \in_array($role_name, Usr::getTenancyAwareDefaultRoles(), true);
    }
}
