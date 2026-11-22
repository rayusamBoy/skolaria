<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\Support\CreatesTestTenant;

uses(RefreshDatabase::class, CreatesTestTenant::class);

afterEach(function () {
    call_user_func([$this, 'destroyTestTenant']);
});

it('requires authentication to access the tenant dashboard', function () {
    config(['tenancy.active_identification_mode' => 'request_data']);

    tenancy()->end();
    Auth::forgetGuards();

    unset($this->defaultCookies['tenant']);

    $response = $this->get('/dashboard');

    $response->assertRedirect('/login');

    expect(tenancy()->initialized)->toBeFalse();
});
