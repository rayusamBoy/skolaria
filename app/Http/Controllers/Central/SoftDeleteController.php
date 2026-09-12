<?php

namespace App\Http\Controllers\Central;

use App\Helpers\Usr;
use App\Http\Controllers\Controller;
use App\Repositories\TenantRepo;
use App\Repositories\UserRepo;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SoftDeleteController extends Controller
{
    public function __construct(protected UserRepo $user, protected TenantRepo $tenant)
    {
        $this->permissionMap = config('permissions.names.central.central_soft_deletes');
        parent::__construct();
    }

    /**
     * Display the central recycle bin with trashed users and tenants.
     */
    public function bin(): View
    {
        $data['users'] = $users = $this->user->getTrashedUsers();
        $data['tenants'] = $tenants = $this->tenant->getTrashedTenants();
        $model_names_to_remove = [];
        if ($users->isEmpty()) {
            $model_names_to_remove[] = 'users';
        }
        if ($tenants->isEmpty()) {
            $model_names_to_remove[] = 'tenants';
        }

        $data['model_names_to_remove'] = $model_names_to_remove;

        return view('pages.central.bin', $data);
    }

    /**
     * Permanently delete all soft‑deleted records of a given model.
     */
    public function empty_soft_deleted_model(string $model_name): RedirectResponse
    {
        $allowed_models = Usr::getCanSoftDeleteModelNames([], false);
        $model_name = strtolower($model_name);

        if (! \in_array($model_name, $allowed_models, true) || ! auth()->user()->hasRole(config('roles.default_roles.central.root'))) {
            return back()->with('pop_error', __('msg.denied'));
        }

        if ($model_name === 'users') {
            $soft_deleted_users = $this->user->getTrashedUsers();
            foreach ($soft_deleted_users as $user) {
                Usr::deleteUserPhotoDirs($user->roles->pluck('name'), $user->code);
            }
            $this->user->forceDeleteOnlyTrashed();
        } else {
            // tenants
            $this->tenant->forceDeleteOnlyTrashed();
        }

        $model_name = str_replace('_', ' ', $model_name);

        return redirect()->back()->with('flash_success', ucfirst($model_name).' '.__('msg.force_del_ok'));
    }

    /********** Users **********/

    public function user_restore(int $id): RedirectResponse
    {
        $this->user->restoreUser($id);

        return redirect()->route('central_bin')->with('flash_success', 'User '.__('msg.restore_ok'));
    }

    public function user_force_delete(int $id): RedirectResponse
    {
        $user = $this->user->findOnlyTrashed($id);
        $this->user->forceDeleteUser($id);
        Usr::deleteUserPhotoDirs($user->roles->pluck('name'), $user->code);

        return redirect()->route('central_bin')->with('flash_success', 'User '.__('msg.force_del_ok'));
    }

    /********** Tenants **********/

    public function tenant_restore(string $id): RedirectResponse
    {
        $this->tenant->restoreTenant($id);

        return redirect()->route('central_bin')->with('flash_success', 'Tenant '.__('msg.restore_ok'));
    }

    public function tenant_force_delete(string $id): RedirectResponse
    {
        $this->tenant->forceDeleteTenant($id);

        return redirect()->route('central_bin')->with('flash_success', 'Tenant '.__('msg.force_del_ok'));
    }
}
