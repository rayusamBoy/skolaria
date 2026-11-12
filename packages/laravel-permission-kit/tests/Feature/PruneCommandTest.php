<?php

use Spatie\Permission\Models\Permission;

it('refuses to prune when disabled', function () {
    config()->set('permission-kit.allow_prune', false);

    $this->artisan('permission-kit:prune', ['--force' => true])
        ->expectsOutputToContain('Pruning is disabled')
        ->assertFailed();
});

it('deletes orphaned permissions', function () {
    config()->set('permission-kit.allow_prune', true);

    Permission::create(['name' => 'orphan.permission', 'guard_name' => 'web']);

    $this->artisan('permission-kit:sync')->assertSuccessful();
    $this->artisan('permission-kit:prune', ['--force' => true])->assertSuccessful();

    $this->assertDatabaseMissing('permissions', ['name' => 'orphan.permission']);
});
