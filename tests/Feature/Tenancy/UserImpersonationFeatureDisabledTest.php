<?php

use App\Http\Controllers\UserImpersonationController;
use App\Repositories\TenantRepo;
use App\Repositories\UserRepo;
use Illuminate\Http\RedirectResponse;
use Mockery;

it('does not initiate impersonation when the feature is disabled', function () {
    config(['tenancy.allow_user_impersonation' => false]);

    $tenantRepo = Mockery::mock(TenantRepo::class);
    $tenantRepo->shouldNotReceive('findOrFailTenant');

    $this->app->instance(TenantRepo::class, $tenantRepo);
    $this->app->instance(UserRepo::class, Mockery::mock(UserRepo::class));

    $response = app(UserImpersonationController::class)
        ->redirect_to_impersonation_route(
            (string) \Illuminate\Support\Str::uuid(),
            1
        );

    expect($response)->toBeInstanceOf(RedirectResponse::class)
        ->and($response->getTargetUrl())->not->toContain('/impersonate/');
});
