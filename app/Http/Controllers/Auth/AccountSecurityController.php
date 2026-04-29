<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Repositories\UserRepo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Jenssegers\Agent\Agent;
use PragmaRX\Google2FALaravel\Google2FA;
use PragmaRX\Recovery\Recovery;

class AccountSecurityController extends Controller
{
    protected UserRepo $user;

    public function __construct(UserRepo $user)
    {
        $this->user = $user;
    }

    /**
     * Display account security settings page.
     */
    public function index(): View
    {
        $google2fa = app('pragmarx.google2fa');
        $twofa_secret_code = $google2fa->generateSecretKey();
        $google2FAIsActive = $this->getGoogle2FASecretKey();

        $qr_image = $google2fa->getQRCodeInline(
            config('app.name'),
            auth()->user()->email ?? Qs::getSetting('system_email'),
            $twofa_secret_code
        );

        $data['twofa_secret_code'] = $twofa_secret_code;
        $data['google2FAIsActive'] = $google2FAIsActive;
        $data['QR_Image'] = $qr_image;
        $data['recovery_codes'] = $this->get_recovery_codes();
        $data['browser_sessions'] = $this->get_browser_sessions();
        $data['users_with_2fa_enabled'] = $this->user->all(['roles'])->whereNotNull('twofa_secret_code');

        return view('auth.2fa.account_security', $data);
    }

    /**
     * Generate and return recovery codes (stored in session).
     */
    public function get_recovery_codes(): array
    {
        $recovery = new Recovery();
        $recovery->setBlocks(3);

        session()->put('twofa_recovery_codes', $recovery->toArray());

        return $recovery->toArray();
    }

    /**
     * Handle account recovery using recovery code.
     */
    public function account_recovery(Request $request): View|RedirectResponse
    {
        if ($request->method() === 'POST') {
            $user = auth()->user();
            $recovery_code = $request->input('recovery_codes');
            $stored_codes = $user->twofa_recovery_codes ?? [];

            if (\in_array($recovery_code, $stored_codes)) {
                $user->update([
                    'twofa_secret_code' => null,
                    'twofa_recovery_codes' => null,
                ]);
                return redirect()->route('index');
            }

            throw ValidationException::withMessages([
                'recovery_codes' => 'Invalid recovery code.',
            ]);
        }

        return view('auth.2fa.account_recovery');
    }

    /**
     * Show Google 2FA verification form.
     */
    public function show_google_2fa_verification(): View
    {
        return view('auth.2fa.verify');
    }

    /**
     * Update user's secret code and recovery codes from session.
     */
    public function update_secret_codes(Request $request): RedirectResponse
    {
        $twofa_recovery_codes = session()->get('twofa_recovery_codes');

        if ($twofa_recovery_codes === null) {
            return redirect()
                ->route('account_security.index')
                ->with('flash_warning', 'The system did not catch the recovery codes correctly. Please refresh the page and try again.');
        }

        $data = [
            'twofa_secret_code' => $request->input('twofa_secret_code'),
            'twofa_recovery_codes' => $twofa_recovery_codes,
        ];

        $this->user->update(auth()->id(), $data);
        session()->forget('twofa_recovery_codes');

        // Mark 2FA as verified for current session
        $google2fa = new Google2FA($request);
        $google2fa->login();

        return back()->with('flash_success', __('msg.update_ok'));
    }

    /**
     * Disable 2FA for a user.
     */
    public function null_secret_code(Request $request): RedirectResponse
    {
        $user_id = $request->input('user', auth()->id());

        if ($this->canNullSecretCode($user_id)) {
            return back()->with('pop_error', __('msg.denied'));
        }

        $this->user->update($user_id, ['twofa_secret_code' => null]);

        return back()->with('flash_success', __('msg.update_ok'));
    }

    /**
     * Redirect to intended URL after successful 2FA verification.
     */
    public function authenticate(): RedirectResponse
    {
        return redirect()->intended();
    }

    /**
     * Get browser sessions for current user.
     */
    public function get_browser_sessions(): Collection
    {
        if (config('session.driver') !== 'database') {
            return collect();
        }

        return $this->getSessionConnection()
            ->where('user_id', auth()->id())
            ->latest('last_activity')
            ->get()
            ->map(fn($session) => $this->formatSessionData($session));
    }

    /**
     * Logout other browser sessions after password confirmation.
     */
    public function logout_other_browser_sessions(): RedirectResponse
    {
        Validator::make(request()->toArray(), [
            'password' => ['required', 'current_password:web'],
        ])->validate();

        Auth::logoutOtherDevices(request()->password);
        $this->deleteOtherSessionRecords();

        return redirect()->back()->with('flash_success', __('msg.other_bs_logout_ok'));
    }

    /**
     * Get user's last activity timestamp.
     */
    public function get_user_last_activity(bool $human = false): Carbon|string
    {
        $last_activity = $this->getSessionConnection()
            ->where('user_id', auth()->id())
            ->latest('last_activity')
            ->first();

        if (!$last_activity) {
            return $human ? 'Never' : now();
        }

        $timestamp = Carbon::createFromTimestamp($last_activity->last_activity);

        return $human ? $timestamp->diffForHumans() : $timestamp;
    }

    // ----------------------------------------------------------------------
    // Protected Helper Methods
    // ----------------------------------------------------------------------

    /**
     * Format session data for display.
     */
    protected function formatSessionData($session): object
    {
        $agent = $this->createAgent($session);

        return (object) [
            'device' => [
                'browser' => $agent->browser(),
                'desktop' => $agent->isDesktop(),
                'mobile' => $agent->isMobile(),
                'tablet' => $agent->isTablet(),
                'platform' => $agent->platform(),
            ],
            'ip_address' => $session->ip_address,
            'is_current_device' => $session->id === request()->session()->getId(),
            'last_active' => Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
        ];
    }

    /**
     * Create an Agent instance from session user agent.
     */
    protected function createAgent($session): Agent
    {
        return tap(new Agent(), fn($agent) => $agent->setUserAgent($session->user_agent));
    }

    /**
     * Delete session records for other devices.
     */
    protected function deleteOtherSessionRecords(): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        $this->getSessionConnection()
            ->where('user_id', auth()->id())
            ->where('id', '!=', request()->session()->getId())
            ->delete();
    }

    /**
     * Get session database connection query builder.
     */
    protected function getSessionConnection()
    {
        return DB::connection(config('session.connection'))
            ->table(config('session.table', 'sessions'));
    }

    /**
     * Check if 2FA is active for authenticated user.
     */
    public function getGoogle2FASecretKey(): bool
    {
        $secret = auth()->user()->twofa_secret_code;
        return $secret !== null && $secret !== '';
    }

    /**
     * Check if current user is allowed to disable 2FA.
     */
    protected function canNullSecretCode($user_id): bool
    {
        return $user_id !== auth()->id() && !Qs::userIsHead();
    }
}
