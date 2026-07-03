<?php

namespace App\Providers;

use App\Helpers\Usr;
use App\Listeners\HandleFailedFcmNotifications;
use App\Listeners\RecordLoginHistory;
use App\Listeners\TenantEventSubscriber;
use Illuminate\Auth\Events\Login;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     */
    protected array $listen = [
        NotificationFailed::class => [
            HandleFailedFcmNotifications::class,
        ],
        Login::class => [
            RecordLoginHistory::class,
        ],
    ];

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
        require_once base_path('routes/tenant/channels.php');

        RateLimiter::for('api', static fn(Request $request) => Limit::perMinute(60)->by($request->user()?->id ?: $request->ip()));
        Event::subscribe(TenantEventSubscriber::class);
        Paginator::useBootstrapFour();
        Gate::before(static fn($user, string $ability) => $user?->hasAnyRole(Usr::getTenancyAwareRootRoles()) ? true : null);
        Model::preventLazyLoading(!app()->isProduction());
    }
}
