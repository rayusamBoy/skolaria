
<?php

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\Support\CreatesTestTenant;

class TestTenantQueueJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        //
    }
}

uses(RefreshDatabase::class, CreatesTestTenant::class);

beforeEach(function () {
    config([
        'queue.default' => 'database',
        'queue.connections.database.connection' => config('database.default'),
        'queue.connections.database.table' => 'jobs',
        'queue.connections.database.after_commit' => false,
    ]);
});

afterEach(function () {
    Auth::forgetGuards();

    call_user_func([$this, 'destroyTestTenant']);
});

it('adds the active tenant context to queued job payloads', function () {
    $tenant = call_user_func([$this, 'createTestTenant']);

    DB::connection(config('database.default'))
        ->table('jobs')
        ->delete();

    Queue::connection('database')->push(
        new TestTenantQueueJob
    );

    $payload = DB::connection(
        config('tenancy.database.central_connection')
    )->table('jobs')->value('payload');

    expect($payload)->not->toBeNull();

    $decodedPayload = json_decode($payload, true);

    expect($decodedPayload)->toBeArray()
        ->and($decodedPayload['tenant_id'] ?? null)->toBe($tenant->id);
});
