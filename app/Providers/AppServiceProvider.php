<?php

namespace App\Providers;

use App\Listeners\HandleFailedFcmNotifications;
use App\Listeners\RecordLoginHistory;
use App\Listeners\TenantEventSubscriber;
use App\Http\Middleware\EnsureApiClientIsActive;
use App\Models\CentralPersonalAccessToken;
use App\Observers\UserPasswordObserver;
use App\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

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
    public function booted(): void
    {
        $middleware = [
            'auth:sanctum',
            EnsureApiClientIsActive::class,
            'abilities:reference-data:read',
        ];

        foreach (['countries.index', 'states.index', 'cities.index'] as $name) {
            $route = Route::getRoutes()->getByName($name);

            if (! $route) {
                throw new \RuntimeException(
                    "The World package route [{$name}] was not registered."
                );
            }

            $route->middleware($middleware);
        }

        // Protect the other World API endpoints as well, if enabled.
        foreach (Route::getRoutes() as $route) {
            if (str_starts_with(
                $route->getActionName(),
                'Nnjeim\\World\\Http\\Controllers\\'
            )) {
                $route->middleware($middleware);
            }
        }
    }

    public function boot(): void
    {
        Sanctum::usePersonalAccessTokenModel(
            CentralPersonalAccessToken::class
        );

        require_once base_path('routes/tenant/channels.php');

        User::observe(UserPasswordObserver::class);

        RateLimiter::for('api', static fn (Request $request) => Limit::perMinute(60)->by($request->user()?->id ?: $request->ip()));
        Event::subscribe(TenantEventSubscriber::class);
        Paginator::useBootstrapFour();
        Model::preventLazyLoading(! app()->isProduction());
    }
}
