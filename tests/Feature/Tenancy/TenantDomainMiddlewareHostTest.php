<?php

use Illuminate\Http\Request;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;

it('extracts the tenant domain from the request host', function () {
    $middleware = app(InitializeTenancyByDomain::class);

    $request = Request::create(
        'https://school-one.skolaria.test/dashboard',
        'GET'
    );

    expect($middleware->getDomain($request))
        ->toBe('school-one.skolaria.test');
});
