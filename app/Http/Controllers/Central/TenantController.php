<?php

namespace App\Http\Controllers\Central;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Http\Requests\TenantRequest;
use App\Repositories\SettingRepo;
use App\Repositories\TenantRepo;
use App\Repositories\UserRepo;
use Illuminate\Support\Carbon;
use Stancl\Tenancy\Resolvers\DomainTenantResolver;

class TenantController extends Controller
{
    protected TenantRepo $tenant;
    protected UserRepo $user;
    protected SettingRepo $setting;

    public function __construct(TenantRepo $tenant, UserRepo $user, SettingRepo $setting)
    {
        $this->permissionMap = config('permissions.names.central.tenants');
        parent::__construct();
        $this->tenant = $tenant;
        $this->user = $user;
        $this->setting = $setting;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data["tenants"] = $this->tenant->getAllTenants();
        $data["user_roles"] = $this->user->getRolesByName();
        $data["active_identification_mode"] = config('tenancy.active_identification_mode');
        $data["active_identification_mode_is_domain"] = (config('tenancy.active_identification_mode') === "domain");

        return view("pages.central.tenants.index", $data);
    }

    public function show($tenant)
    {
        $data["tenant"] = $this->tenant->findTenant($tenant);

        return view("pages.central.tenants.show", $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TenantRequest $request)
    {
        set_time_limit(600);

        $data = $request->except(['_token', '_method', 'domain', 'tenant_slug']);
        $data['identification_mode'] = $request->identification_mode;

        try {
            $tenant = $this->tenant->createTenant($data);
        } catch (\Throwable $t) {
            logger()->error('Tenant creation failed', ['error' => $t->getMessage()]);
            return Qs::json(msg: "", arr: ['msg' => $t->getMessage(), 'ok' => false, 'pop' => true, 'pop_timer' => 0]);
        }

        // Handle domain or request‑data fields
        if ($request->identification_mode === 'domain') {
            try {
                $tenant->createDomain(['domain' => $request->domain]);
            } catch (\Throwable $t) {
                logger()->error('Domain creation failed', ['error' => $t->getMessage()]);
                return Qs::json(msg: "", arr: ['msg' => $t->getMessage(), 'ok' => false, 'pop' => true, 'pop_timer' => 0]);
            }
        } else {
            $tenant->update([
                'tenant_slug' => $request->tenant_slug,
            ]);
        }

        tenancy()->run($tenant, function () use ($request) {
            $this->setting->update('system_name', $request->name);
            $this->setting->update('system_title', Qs::getStringAbbreviation($request->name));
        });

        return Qs::json(msg: "", arr: ['msg' => __('msg.store_ok'), 'ok' => true, 'pop' => true, 'pop_timer' => 0]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($tenant)
    {
        $data["tenant"] = $this->tenant->findTenant($tenant);

        return view("pages.central.tenants.edit", $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TenantRequest $request, $tenant)
    {
        $tenant = $this->tenant->findTenant($tenant);

        $data = $request->only(['account_status', 'payment_status', 'remarks', 'name']);
        $data['updated_at'] = Carbon::now();

        if ($tenant->identification_mode === 'domain') {
            // Domain‑based tenant – update domain record
            if ($request->has('domain') && $request->domain) {
                app(DomainTenantResolver::class)->invalidateCache($tenant);
                $this->tenant->updateOrCreateDomain(
                    ['tenant_id' => $tenant->id],
                    ['domain' => $request->domain]
                );
            }
        } else {
            $data['tenant_slug'] = $request->tenant_slug;
        }

        $tenant->update($data);

        return Qs::json(msg: "", arr: ['msg' => __('msg.update_ok'), 'ok' => true, 'pop' => true]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($tenant)
    {
        $tenant = $this->tenant->findTenant($tenant);
        try {
            $tenant->delete();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['delete_error' => $e->getMessage()]);
        }

        return back()->with('pop_success', __('msg.del_ok'))->with('pop_timer', 0);
    }
}
