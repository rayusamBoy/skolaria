<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Queue\CallQueuedClosure;
use Illuminate\Support\Facades\Queue;
use Tests\Support\CreatesTestTenant;

uses(RefreshDatabase::class, CreatesTestTenant::class);

afterEach(function () {
    call_user_func([$this, 'destroyTestTenant']);
});

it('dispatches queued closures without changing the active tenant context', function () {
    Queue::fake();

    config(['tenancy.active_identification_mode' => 'request_data']);

    $tenant = call_user_func([$this, 'createTestTenant']);

    $tenantId = tenant('id');

    dispatch(function () {
        //
    });

    Queue::assertPushed(CallQueuedClosure::class);

    expect(tenancy()->initialized)->toBeTrue()
        ->and(tenant('id'))->toBe($tenantId)
        ->and(tenant('id'))->toBe($tenant->id);
});
