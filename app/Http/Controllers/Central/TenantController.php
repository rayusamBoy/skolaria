<?php

namespace App\Http\Controllers\Central;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Http\Requests\TenantRequest;
use App\Repositories\TenantRepo;
use App\Repositories\UserRepo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Stancl\Tenancy\Resolvers\DomainTenantResolver;

class TenantController extends Controller
{
    protected $tenant, $user;
    public function __construct(TenantRepo $tenant, UserRepo $user)
    {
        $this->permissionMap = config('permissions.names.central.tenants');
        parent::__construct();
        $this->tenant = $tenant;
        $this->user = $user;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data["tenants"] = $this->tenant->getAll();
        $data["user_roles"] = $this->user->getRoles();

        return view("pages.central.tenants.index", $data);
    }

    public function show($tenant)
    {
        $data["tenant"] = $this->tenant->find($tenant);

        return view("pages.central.tenants.show", $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TenantRequest $request)
    {
        set_time_limit(600); // Set time limit for this method to 10 minutes.
        $data = $request->except(['_token', '_method', 'domain']);
        $cleaning_error = $creation_error = $domain_error = $tenant = null;

        try {
            $tenant = $this->tenant->create($data);
        } catch (\Throwable $t) {
            $creation_error = $t->getMessage();
            if (session()->has("created_tenant_id")) {
                try {
                    $this->tenant->delete(session()->get("created_tenant_id"));
                } catch (\Throwable $t) {
                    $cleaning_error = $t->getMessage();
                }
                session()->forget("created_tenant_id");
            }
        }

        if ($tenant) {
            try {
                $tenant->createDomain(['domain' => $request->domain]);
            } catch (\Throwable $t) {
                $domain_error = $t->getMessage();
            }
        } else {
            $domain_error = "Tenant creation failed, so domain creation skipped.";
        }

        $errors = [];

        if ($creation_error) {
            $errors['tenant_creation_error'] = $creation_error;
        }

        if ($cleaning_error) {
            $errors['tenant_cleaning_error'] = $cleaning_error;
        }

        if ($domain_error) {
            $errors['domain_creation_error'] = $domain_error;
        }

        if ($creation_error || $cleaning_error || $domain_error) {
            Log::error("Errors during tenant creation process: ", $errors);
            return Qs::json(null, null, ['msg' => implode(", ", $errors), 'ok' => false, 'pop' => true, 'pop_timer' => 0]);
        }

        return Qs::json(null, null, ['msg' => __('msg.store_ok'), 'ok' => true, 'pop' => true, 'pop_timer' => 0]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($tenant)
    {
        $data["tenant"] = $this->tenant->find($tenant);

        return view("pages.central.tenants.edit", $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TenantRequest $request, $tenant)
    {
        $tenant = $this->tenant->find($tenant);

        $data = $request->only(['account_status', 'payment_status', 'remarks']);
        $data['updated_at'] = Carbon::now();

        // Handle domain update if provided
        if ($request->has('domain') && $request->domain !== $tenant->domain) {
            // Invalidate the cached domain for this tenant
            app(DomainTenantResolver::class)->invalidateCache($tenant);
            $this->tenant->updateDomain(['tenant_id' => $tenant->id], ['domain' => $request->domain]);
            $data['domain'] = $request->domain;
        }

        $tenant->update($data);

        return Qs::json(null, null, ['msg' => __('msg.update_ok'), 'ok' => true, 'pop' => true]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($tenant)
    {
        $tenant = $this->tenant->find($tenant);
        try {
            $tenant->delete();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['delete_error' => $e->getMessage()]);
        }

        return back()->with('pop_success', __('msg.del_ok'))->with('pop_timer', 0);
    }
}
