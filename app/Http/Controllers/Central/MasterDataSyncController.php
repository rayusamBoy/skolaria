<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Repositories\TenantRepo;
use App\Services\SyncMasterDataService;
use Illuminate\Http\Request;
use App\Auth\Attributes\SkipPermission;

class MasterDataSyncController extends Controller
{
    protected TenantRepo $tenant;

    public function __construct(TenantRepo $tenant)
    {
        $this->permissionMap = config('permissions.names.central.master_data_syncs');
        parent::__construct();
        $this->tenant = $tenant;
    }

    /**
     * Display a listing of the resource.
     */
    #[SkipPermission]
    public function index()
    {
        $this->validateRequestDataMode('domain');

        $tenants = $this->tenant->getAllTenants(false);
        $tables = new SyncMasterDataService()->getSyncableTables();
        $templateTenant = $this->tenant->whereTenantQuery(['data->is_template' => 1])->first();

        return view('pages.central.master_data_sync', compact('tenants', 'tables', 'templateTenant'));
    }

    /**
     * Sync master data between template tenant and selected tenant(s).
     */
    public function sync(Request $request, SyncMasterDataService $service)
    {
        $this->validateRequestDataMode('domain');

        $validated = $request->validate([
            'source_tenant_id' => 'required|exists:tenants,id',
            'target_tenants' => 'required|array',
            'target_tenants.*' => 'exists:tenants,id',
            'tables' => 'required|array',
            'tables.*' => 'in:' . implode(',', $service->getSyncableTables()),
            'lock_after_sync' => 'boolean',
            'exclude_lock_tenants' => 'nullable|array',
            'exclude_lock_tenants.*' => 'exists:tenants,id',
        ]);

        $sourceTenant = $this->tenant->findOrFailTenant($validated['source_tenant_id']);
        $targetIds = $validated['target_tenants'];
        $tables = $validated['tables'];
        $lock = $request->boolean('lock_after_sync');
        $exclude = $validated['exclude_lock_tenants'] ?? [];

        $service->sync($sourceTenant, $targetIds, $tables, $lock, $exclude);

        return redirect()->route('tenants.index')->with('pop_success', 'Master data synced successfully.');
    }

    /**
     * Validate request data mode.
     */
    private function validateRequestDataMode(string $mode): void
    {
        if ($mode !== 'request_data') {
            throw new \InvalidArgumentException("Tenant identification mode must be 'request_data' to access this endpoint.");
        }
    }
}
