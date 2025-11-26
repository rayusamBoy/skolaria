<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromUnwantedDomains;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__ . '/../routes/console.php',
        using: function () {
            $centralDomains = config('tenancy.identification.central_domains');

            foreach ($centralDomains as $domain) {
                Route::middleware('web')
                    ->domain($domain)
                    ->namespace('App\Http\Controllers')
                    ->group(base_path('routes/web.php'));
            }

            Route::middleware([
                'tenant',
                InitializeTenancyByDomain::class,
                PreventAccessFromUnwantedDomains::class
            ])->group(function () {
                Route::middleware('web')->namespace('App\Http\Controllers')->group(base_path('routes/tenant/web.php'));
                Route::middleware('api')->group(base_path('routes/tenant/api.php'));
                Route::middleware('web')->group(base_path('routes/tenant/channels.php'));
            });
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
        $middleware->alias([
            'checkForPassUpdate' => \App\Http\Middleware\Custom\CheckForPasswordUpdate::class,
            'handleCookie' => \App\Http\Middleware\Custom\HandleCookie::class,
            'examIsLocked' => \App\Http\Middleware\Custom\ExamIsLocked::class,
            '2fa' => \PragmaRX\Google2FALaravel\Middleware::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'roleOrPermission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
        $middleware->encryptCookies(except: [
            'do_not_show_els_with_these_ids_again',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
