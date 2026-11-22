<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\CreatesTestTenant;

uses(RefreshDatabase::class, CreatesTestTenant::class);

afterEach(function () {
    call_user_func([$this, 'destroyTestTenant']);
});

it('restores the central database connection after ending tenant context', function () {
    $centralConnection = DB::getDefaultConnection();
    $centralDatabase = DB::connection($centralConnection)->getDatabaseName();

    config(['tenancy.active_identification_mode' => 'request_data']);

    call_user_func([$this, 'createTestTenant']);

    expect(tenancy()->initialized)->toBeTrue()
        ->and(DB::getDefaultConnection())->not->toBe($centralConnection);

    tenancy()->end();

    expect(tenancy()->initialized)->toBeFalse()
        ->and(DB::getDefaultConnection())->toBe($centralConnection)
        ->and(DB::connection()->getDatabaseName())->toBe($centralDatabase);
});
