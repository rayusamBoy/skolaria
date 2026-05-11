<?php

namespace App\Http\Middleware\Custom;

use App\Helpers\Usr;
use App\Services\CookieService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response as IlluminateResponse;
use Symfony\Component\HttpFoundation\Response;

class HandleCookie
{
    protected CookieService $cookie_service;

    public function __construct(CookieService $cookie_service)
    {
        $this->cookie_service = $cookie_service;
    }

    /**
     * Handle an incoming request.
     *
     * Sets cookies for hidden alert IDs and active system skin based on user
     * preferences or defaults. Cookies are queued on the response.
     *
     * @param  Request  $request
     * @param  Closure(Request): Response  $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Hidden alert IDs: use user value or empty
        $hidden_alert_ids = $user?->hidden_alert_ids
            ? json_encode($user->hidden_alert_ids)
            : '';

        // Active system skin: user preference > existing cookie > default first skin
        $active_system_skin = $this->resolveActiveSystemSkin($user, $request);

        $hidden_alerts_cookie = $this->cookie_service->setForever(
            name: 'do_not_show_els_with_these_ids_again',
            value: $hidden_alert_ids,
            httpOnly: false
        );

        $active_skin_cookie = $this->cookie_service->setForever(
            name: 'active_system_skin',
            value: json_encode($active_system_skin),
            httpOnly: false
        );

        $response = $next($request);

        // Check if the response is an instance of Illuminate\Http\Response (HTTP responses)
        if ($response instanceof IlluminateResponse) {
            return $response->withCookie($hidden_alerts_cookie)->withCookie($active_skin_cookie);
        }

        return $response;
    }

    /**
     * Determine the active system skin value.
     */
    protected function resolveActiveSystemSkin($user, Request $request): string
    {
        // User preference stored in preferences array
        if ($user && isset($user->preferences['active_system_skin'])) {
            return $user->preferences['active_system_skin'];
        }

        // Existing cookie value (decode from JSON)
        $cookie_value = $request->cookie('active_system_skin');
        if ($cookie_value) {
            $decoded = json_decode($cookie_value, true);
            if (\is_string($decoded)) {
                return $decoded;
            }
        }

        // Default: first available skin key
        $available_skins = Usr::getAvailableAppSkins();
        return array_key_first($available_skins) ?? 'default';
    }
}
