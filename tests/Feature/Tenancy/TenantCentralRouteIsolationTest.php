
<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\Support\CreatesTestTenant;

uses(RefreshDatabase::class, CreatesTestTenant::class);

beforeEach(function () {
    config([
        'tenancy.active_identification_mode' => 'request_data',
    ]);
});

afterEach(function () {
    Auth::forgetGuards();

    call_user_func([$this, 'destroyTestTenant']);
});

it('keeps central routes in central context even when a tenant cookie is present', function () {
    $tenant = call_user_func([$this, 'createTestTenant']);

    tenancy()->end();

    app('session')->forgetDrivers();
    Auth::forgetGuards();

    Route::get('/__central-context-probe', function () {
        return response()->json([
            'tenant_initialized' => tenancy()->initialized,
            'default_connection' => config('database.default'),
            'connection_name' => DB::connection()->getName(),
        ]);
    })->middleware('web');

    $centralConnection = config('tenancy.database.central_connection');

    $this->withCookie('tenant', $tenant->id)
        ->get('/__central-context-probe')
        ->assertSuccessful()
        ->assertJson([
            'tenant_initialized' => false,
            'default_connection' => $centralConnection,
            'connection_name' => $centralConnection,
        ]);
});
