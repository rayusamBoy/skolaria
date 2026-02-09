<?php

namespace App\Providers;

use App\Helpers\Usr;
use App\Listeners\TenantEventSubscriber;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Opcodes\LogViewer\Facades\LogViewer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        require base_path('routes/tenant/channels.php');

        Route::bind('id', Usr::decodeHash(...));
        RateLimiter::for('api', fn(Request $request) => Limit::perMinute(60)->by($request->user()?->id ?: $request->ip()));
        LogViewer::auth(fn($request) => $request?->user()?->hasAnyRole(array_values(config('roles.root_roles.central'))));
        Event::subscribe(TenantEventSubscriber::class);
        Paginator::useBootstrapFour();
        View::share('central_permissions', config('permissions.names.central'));
        View::share('tenant_permissions', config('permissions.names.tenant'));

        // Implicitly grant root roles all permissions
        // This works in the app that uses gate-related functions like auth()->user->can() and @can()
        Gate::before(fn($user, $ability) => \in_array($user->getRoleNames(), Usr::getTenancyAwareRootRoles()) ? true : null);
    }
}
