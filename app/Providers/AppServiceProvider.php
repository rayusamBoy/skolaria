<?php

namespace App\Providers;

use App\Helpers\Qs;
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

        Route::bind('id', fn($value) => Qs::decodeHash($value));
        RateLimiter::for('api', fn(Request $request) => Limit::perMinute(60)->by($request->user()?->id ?: $request->ip()));
        LogViewer::auth(fn($request) => $request->user() && $request->user()->hasRole('Itguy'));
        Event::subscribe(TenantEventSubscriber::class);
        Paginator::useBootstrapFour();
        View::share('central_permissions', config('permissions.names.central'));
        View::share('tenant_permissions', config('permissions.names.tenant'));

        // Implicitly grant "super_admin" role all permissions
        // This works in the app by using gate-related functions like auth()->user->can() and @can()
       // Gate::before(fn($user, $ability) => $user->hasRole('super_admin') || $user->hasRole('Itguy') ? true : null);
        Gate::before(fn($user, $ability) => true);
    }
}
