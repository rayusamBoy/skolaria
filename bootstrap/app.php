<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\InitializeTenancyByRequestData;
use Stancl\Tenancy\Middleware\PreventAccessFromUnwantedDomains;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__ . '/../routes/console.php',
        using: function () {
            $centralDomains = config('tenancy.identification.central_domains');
            $activeIdentificationMode = config('tenancy.active_identification_mode');

            if ($activeIdentificationMode === 'domain') {
                foreach ($centralDomains as $domain) {
                    Route::middleware('web')
                        ->domain($domain)
                        ->namespace('App\Http\Controllers')
                        ->group(base_path('routes/web.php'));
                }

                Route::middleware([
                    InitializeTenancyByDomain::class,
                    PreventAccessFromUnwantedDomains::class
                ])->group(function () {
                    Route::middleware('web')
                        ->namespace('App\Http\Controllers')
                        ->group(base_path('routes/tenant/web.php'))
                        ->group(base_path('routes/tenant/universal.php'));
                    Route::middleware('api')->group(base_path('routes/tenant/api.php'));
                    Route::middleware('web')->group(base_path('routes/tenant/channels.php'));
                });
            } else {
                Route::middleware(['web', InitializeTenancyByRequestData::class])
                    ->namespace('App\Http\Controllers')
                    ->group(base_path('routes/tenant/universal.php'))
                    ->group(base_path('routes/tenant/web.php'));

                Route::middleware('web')
                    ->namespace('App\Http\Controllers')
                    ->group(base_path('routes/web.php'));
            }
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web([
            \App\Http\Middleware\Custom\LoadCommonContext::class,
            \App\Http\Middleware\Custom\HandleCookie::class,
        ]);
        $middleware->alias([
            'checkForPassUpdate' => \App\Http\Middleware\Custom\CheckForPasswordUpdate::class,
            '2fa' => \PragmaRX\Google2FALaravel\Middleware::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'roleOrPermission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'decodeRouteParams' => \App\Http\Middleware\Custom\DecodeRouteParams::class,
        ]);
        $middleware->encryptCookies(except: [
            'do_not_show_els_with_these_ids_again',
            'active_system_skin',
        ]);
        $middleware->prependToPriorityList(
            before: \Illuminate\Routing\Middleware\SubstituteBindings::class,
            prepend: \App\Http\Middleware\Custom\DecodeRouteParams::class,
        );
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
