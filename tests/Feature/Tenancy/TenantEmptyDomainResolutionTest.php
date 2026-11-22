<?php

use Illuminate\Http\Request;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedOnDomainException;

it('does not resolve a tenant when the domain is empty', function () {
    $middleware = app(InitializeTenancyByDomain::class);

    expect(fn () => $middleware->initializeTenancy(
        Request::create('/'),
        fn () => response('Unexpected tenant'),
        ''
    ))->toThrow(TenantCouldNotBeIdentifiedOnDomainException::class);

    expect(tenancy()->initialized)->toBeFalse();
});
