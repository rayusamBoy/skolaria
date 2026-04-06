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

        $centralRootRoles = array_values(config('roles.root_roles.central', []));
        $rootRoles = Usr::getTenancyAwareRootRoles();

        RateLimiter::for('api', static fn(Request $request) => Limit::perMinute(60)->by($request->user()?->id ?? $request->ip()));
        LogViewer::auth(static fn($request) => (bool) $request?->user()?->hasAnyRole($centralRootRoles));
        Event::subscribe(TenantEventSubscriber::class);
        Paginator::useBootstrapFour();
        Gate::before(fn($user, string $ability) => $user?->hasAnyRole($rootRoles) ? true : null);
    }
}
