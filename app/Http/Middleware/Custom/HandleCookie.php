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
    protected $cookieService;
    public function __construct(CookieService $cookieService)
    {
        $this->cookieService = $cookieService;
    }

    /**
     * Handle an incoming request.
     * Sets cookies for hidden alert IDs and active system skin based on user preferences or defaults.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        // These are casted as array, so we convert to json
        $hiddenAlertIds = $user?->hidden_alert_ids ? json_encode($user?->hidden_alert_ids) : "";
        $activeSystemSkin = \array_key_exists('active_system_skin', $user?->preferences ?? []) ? json_encode($user->preferences['active_system_skin']) : $request->cookie('active_system_skin', json_encode(array_key_first(Usr::getAvailableAppSkins())));

        $hiddenAlertIdsCookie = $this->cookieService->setForever('do_not_show_els_with_these_ids_again', $hiddenAlertIds, httpOnly: false);
        $activeSystemSkinCookie = $this->cookieService->setForever('active_system_skin', $activeSystemSkin, httpOnly: false);

        $response = $next($request);

        // Check if the response is an instance of Illuminate\Http\Response (HTTP responses)
        if ($response instanceof IlluminateResponse) {
            return $response->withCookie($hiddenAlertIdsCookie)->withCookie($activeSystemSkinCookie); // Only add cookie to Illuminate's Response
        }

        // Return the response as is for other types like BinaryFileResponse
        return $response;
    }
}
