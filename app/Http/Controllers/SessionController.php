<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class SessionController extends Controller
{
    /** Return current expiry + remember flag. */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['ok' => false], 401);
        }

        $remember = (string) $user->getRememberToken() !== '';
        $lifetime = $remember
            ? (int) config('auth.remember_ttl', 60 * 24 * 14) * 60
            : (int) config('session.lifetime') * 60;

        return response()->json([
            'ok' => true,
            'expires_at' => now()->addSeconds($lifetime)->getTimestamp(),
            'remember' => $remember,
        ]);
    }

    /** Extend the session. */
    public function heartbeat(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['ok' => false], 401);
        }

        $request->session()->put('_last_heartbeat', now()->getTimestamp());

        $remember = (string) $user->getRememberToken() !== '';
        $lifetime = $remember
            ? (int) config('auth.remember_ttl', 60 * 24 * 14) * 60
            : (int) config('session.lifetime') * 60;

        return response()->json([
            'ok' => true,
            'expires_at' => now()->addSeconds($lifetime)->getTimestamp(),
            'remember' => $remember,
        ]);
    }

    /**
     * Called when the client detects the session has expired.
     * Force-logs out, stores the current URL for post-reauth redirection,
     * then sends the user to the login page in reauth mode.
     */
    public function expired(Request $request)
    {
        // Only accept same-origin return URLs
        $return = $request->query('return');
        $safe_return = null;

        if (\is_string($return) && $return !== '') {
            $parts = parse_url($return);
            $app = parse_url(config('app.url'));
            if (($parts['host'] ?? null) === ($app['host'] ?? null)) {
                $safe_return = $return;
            }
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($safe_return) {
            session(['url.intended' => $safe_return]);
        }

        return redirect()->route('login', ['reason' => 'timeout']);
    }

    /**
     * Password-only re-authentication after a timeout.
     * Verifies the signed token (identifies the user) and the password.
     */
    public function reauth(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => ['required', 'integer'],
            'password' => ['required', 'string'],
            'token' => ['required', 'string'],
        ]);

        // Verify the signed token
        $expected = hash_hmac('sha256', (string) $request->user_id, config('app.key'));
        if (! hash_equals($expected, $request->token)) {
            return response()->json(['ok' => false, 'msg' => 'Invalid credentials.'], 401);
        }

        // Rate limit (5 / minute per user+IP)
        $rlKey = "reauth:{$request->user_id}:".$request->ip();
        if (RateLimiter::tooManyAttempts($rlKey, 5)) {
            return response()->json([
                'ok' => false,
                'msg' => 'Too many attempts. Please try again later.',
            ], 429);
        }

        // Verify user + password
        $user = User::find($request->user_id);
        if (! $user || ! Hash::check($request->password, $user->password)) {
            RateLimiter::hit($rlKey, 60);

            return response()->json(['ok' => false, 'msg' => 'Incorrect password.'], 401);
        }

        RateLimiter::clear($rlKey);

        // Log the user in and resume where they left off
        Auth::login($user, false);
        $request->session()->regenerate();
        $request->session()->put('_last_heartbeat', now()->getTimestamp());

        $intended = session()->pull('url.intended', route('dashboard'));

        return response()->json([
            'ok' => true,
            'redirect' => $intended,
            'expires_at' => now()->addSeconds((int) config('session.lifetime') * 60)->getTimestamp(),
            'remember' => false,
        ]);
    }
}
