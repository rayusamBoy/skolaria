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
use Illuminate\Database\Eloquent\Model;

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

        RateLimiter::for('api', static fn(Request $request) => Limit::perMinute(60)->by($request->user()?->id ?? $request->ip()));
        Event::subscribe(TenantEventSubscriber::class);
        Paginator::useBootstrapFour();
        Gate::before(fn($user, string $ability) => $user?->hasAnyRole(Usr::getTenancyAwareRootRoles()) ? true : null);

        Model::preventLazyLoading(!app()->isProduction());
    }
}
