<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\Usr;
use App\Http\Controllers\Controller;
use App\Services\CookieService;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller implements HasMiddleware
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Create a new controller instance.
     *
     * @return void$field
     */
    public function __construct(protected CookieService $cookieService) {}

    /**
     * Show the application's login form.
     *
     * @return View
     */
    public function showLoginForm()
    {
        $account_status = tenant('account_status');

        // If account not active ie., blocked, suspended etc., abort as forbidden.
        if (\in_array($account_status, Usr::getAccountStatuses(['Active']))) {
            $message = 'ACCOUNT '.strtoupper($account_status);
            session()->flash('tenant_account_not_active', $message);
            abort(403, $message);
        }

        return view('auth.login');
    }

    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('guest', except: ['logout']),
            new Middleware('auth', only: ['logout']),
        ];
    }

    /**
     *  Login with Username or Email
     */
    public function username()
    {
        $identity = request()->identity;
        $field = filter_var($identity, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        request()->merge([$field => $identity]);

        return $field;
    }

    /**
     * The user has logged out of the application.
     *
     * @return mixed
     */
    protected function loggedOut(Request $request)
    {
        // Set cookie - has logged out
        $this->cookieService->setForever('has_logged_out', true);
        // Delete was logged in
        $this->cookieService->delete('was_logged_in');

        session()->forget('impersonated_from_central');

        return redirect()->route('login');
    }

    /**
     * The user has been authenticated.
     *
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        $user ??= auth()->user();

        if ($user->is_blocked) {
            Auth::logout();

            return redirect()->route('login')
                ->with('access_denied', __('msg.restricted').' ACCOUNT BLOCKED.');
        }

        return redirect()->route(Usr::tenancyInitialized() ? 'dashboard' : 'central_dashboard');
    }
}
