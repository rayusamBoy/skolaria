
<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Support\CreatesTestTenant;

uses(RefreshDatabase::class, CreatesTestTenant::class);

beforeEach(function () {
    config([
        'tenancy.active_identification_mode' => 'request_data',
        'session.driver' => 'database',
        'session.connection' => null,
    ]);
});

afterEach(function () {
    app('session')->forgetDrivers();
    Auth::forgetGuards();

    call_user_func([$this, 'destroyTestTenant']);
});

it('isolates session records between tenant databases', function () {
    $tenantA = call_user_func([$this, 'createTestTenant']);

    $sessionId = (string) Str::uuid();

    DB::connection('tenant')->table('sessions')->insert([
        'id' => $sessionId,
        'user_id' => null,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Pest Test',
        'payload' => base64_encode(serialize([
            'tenant_secret' => 'Tenant A secret',
        ])),
        'last_activity' => time(),
    ]);

    expect(
        DB::connection('tenant')
            ->table('sessions')
            ->where('id', $sessionId)
            ->exists()
    )->toBeTrue();

    tenancy()->end();

    app('session')->forgetDrivers();
    Auth::forgetGuards();

    $tenantB = call_user_func([$this, 'createTestTenant']);

    expect($tenantB->id)->not->toBe($tenantA->id);

    expect(
        DB::connection('tenant')
            ->table('sessions')
            ->where('id', $sessionId)
            ->exists()
    )->toBeFalse();

    tenancy()->end();

    app('session')->forgetDrivers();
    Auth::forgetGuards();

    tenancy()->initialize($tenantA);

    expect(
        DB::connection('tenant')
            ->table('sessions')
            ->where('id', $sessionId)
            ->exists()
    )->toBeTrue();
});
