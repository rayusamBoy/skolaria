<?php

namespace App\Http\Controllers\Central;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Http\Requests\TenantRequest;
use App\Repositories\TenantRepo;
use App\Repositories\UserRepo;
use Stancl\Tenancy\Resolvers\DomainTenantResolver;
use Illuminate\Support\Carbon;

class TenantController extends Controller
{
    protected $tenant, $user;
    public function __construct(TenantRepo $tenant, UserRepo $user)
    {
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

    public function show($id)
    {
        $id = Qs::decodeHash($id);
        $data["tenant"] = $this->tenant->find($id);

        return view("pages.central.tenants.show", $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TenantRequest $request)
    {
        set_time_limit(600); // Set time limit for this method to 10 minutes.
        $data = $request->except(['_token', '_method', 'domain']);

        try {
            $tenant = $this->tenant->create($data);
        } catch (\Exception $e) {
            $cleaning_error = null;

            try {
                if (session()->has("created_tenant_id")) {
                    $this->tenant->delete(session()->get("created_tenant_id"));
                    session()->forget("created_tenant_id");
                }
            } catch (\Throwable $t) {
                $cleaning_error = $t->getMessage();
            }

            return redirect()->back()->withErrors(['tenant_create_error' => $e->getMessage(), 'cleaning_error' => $cleaning_error]);
        }

        $tenant->createDomain(['domain' => $request->domain]);

        session()->forget("created_tenant_id");

        return back()->with('pop_success', __('msg.store_ok'))->with('pop_timer', 0);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $id = Qs::decodeHash($id);
        $data["tenant"] = $this->tenant->find($id);

        return view("pages.central.tenants.edit", $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TenantRequest $request, string $id)
    {
        $id = Qs::decodeHash($id);
        $tenant = $this->tenant->find($id);

        $data = $request->only(['account_status', 'payment_status', 'remarks']);
        $data['updated_at'] = Carbon::now();

        // Handle domain update if provided
        if ($request->has('domain') && $request->domain !== $tenant->domain) {
            // Invalidate the cached domain for this tenant
            app(DomainTenantResolver::class)->invalidateCache($tenant);
            $this->tenant->updateDomain(['tenant_id' => $tenant->id], ['domain' => $request->domain]);
            $data['domain'] = $request->domain;
        }

        $this->tenant->update($id, $data);

        return Qs::json(null, null, ['msg' => __('msg.update_ok'), 'ok' => true, 'pop' => true]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $id = Qs::decodeHash($id);
        try {
            $this->tenant->delete($id);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['delete_error' => $e->getMessage()]);
        }

        return back()->with('pop_success', __('msg.del_ok'))->with('pop_timer', 0);
    }
}
