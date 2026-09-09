<?php

use App\Http\Middleware\Custom\CheckForPasswordUpdate;
use App\Http\Middleware\Custom\DecodeRouteParams;
use App\Http\Middleware\Custom\HandleCookie;
use App\Http\Middleware\Custom\LoadCommonContext;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\InitializeTenancyByRequestData;
use Stancl\Tenancy\Middleware\PreventAccessFromUnwantedDomains;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
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
                    PreventAccessFromUnwantedDomains::class,
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
            LoadCommonContext::class,
            HandleCookie::class,
        ]);
        $middleware->alias([
            'checkForPassUpdate' => CheckForPasswordUpdate::class,
            '2fa' => PragmaRX\Google2FALaravel\Middleware::class,
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'roleOrPermission' => RoleOrPermissionMiddleware::class,
            'decodeRouteParams' => DecodeRouteParams::class,
        ]);
        $middleware->encryptCookies(except: [
            'do_not_show_els_with_these_ids_again',
            'active_system_skin',
        ]);
        $middleware->prependToPriorityList(
            before: SubstituteBindings::class,
            prepend: DecodeRouteParams::class,
        );
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
