<?php

namespace App\Http\Controllers;

use App\Repositories\PermissionRepo;
use Illuminate\Http\Request;
use Qs;

class RoleController extends Controller
{
    protected $permission;
    public function __construct(PermissionRepo $permission)
    {
        $this->permission = $permission;
    }

    public function index()
    {
        $data['permissions'] = $this->permission->getPermissions();
        $data['roles'] = $this->permission->getRoles();

        return view('pages.roles.index', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'unique:roles,name',
            ],
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role_name = ucwords($request->name);
        $role = $this->permission->createRole(['name' => $role_name]);

        if ($request->filled('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return Qs::jsonStoreOk();
    }

    public function edit($id)
    {
        $data['permissions'] = $this->permission->getPermissions();
        $data['role'] = $role = $this->permission->getRole(['id' => $id]);
        $data['rolePermissions'] = $role->permissions->pluck('id')->toArray();

        return view('pages.roles.edit', $data);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                "unique:roles,name,{$id}",
            ],
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = $this->permission->getRole(['id' => $id]);
        $role_name = ucwords($request->name);
        $role->update(['name' => $role_name]);
        $role->syncPermissions($request->permissions ?? []);

        return redirect()->route('roles.index')->with('flash_success', __('msg.update_ok'));
    }

    public function destroy($id)
    {
        $this->permission->deleteRole(['id' => $id]);

        return redirect()->back()->with('flash_success', __('msg.del_ok'));
    }
}
