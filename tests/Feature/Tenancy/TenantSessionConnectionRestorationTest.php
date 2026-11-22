
<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestTenant;

uses(RefreshDatabase::class, CreatesTestTenant::class);

afterEach(function () {
    call_user_func([$this, 'destroyTestTenant']);
});

it('restores the central session connection after ending tenant context', function () {
    config([
        'session.driver' => 'database',
        'session.connection' => 'central',
    ]);

    $centralConnection = config('session.connection');

    call_user_func([$this, 'createTestTenant']);

    expect(config('session.connection'))->toBe('tenant');

    tenancy()->end();

    expect(config('session.connection'))->toBe($centralConnection);
});
