<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CreatesTestTenant;

uses(RefreshDatabase::class, CreatesTestTenant::class);

afterEach(function () {
    call_user_func([$this, 'destroyTestTenant']);
});

it('restores the central filesystem path after ending tenant context', function () {
    $centralPath = Storage::disk('local')->path('');

    call_user_func([$this, 'createTestTenant']);

    $tenantPath = Storage::disk('local')->path('');

    expect($tenantPath)->not->toBe($centralPath);

    tenancy()->end();

    expect(Storage::disk('local')->path(''))->toBe($centralPath);
});
