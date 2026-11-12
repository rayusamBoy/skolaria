<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Http\Requests\Pin\PinCreate;
use App\Http\Requests\Pin\PinVerify;
use App\Repositories\PinRepo;
use App\Repositories\UserRepo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;
use PermissionKit\Concerns\AuthorizesPermissions;

class PinController extends Controller
{
    use AuthorizesPermissions;

    public function __construct(protected PinRepo $pin, protected UserRepo $user) {}

    /**
     * Display a listing of pins (valid and used).
     */
    public function index(): View
    {
        $data['pin_count'] = $this->pin->countValidPins();
        $data['valid_pins'] = $this->pin->getValidPins();
        $data['used_pins'] = $this->pin->getUsedPins();

        return view('pages.pins.index', $data);
    }

    /**
     * Show the form for creating new pins.
     */
    public function create(): View|RedirectResponse
    {
        if ($this->pin->countValidPins() > 500) {
            return redirect()->route('pins.index')->with('flash_danger', __('msg.pin_max'));
        }

        return view('pages.pins.create');
    }

    /**
     * Show the PIN entry form for a student.
     */
    public function enter_pin(int $student_id): View|RedirectResponse
    {
        if (
            auth()->user()->hasAnyRole([
                config('roles.root_roles.tenant.superadmin'),
                config('roles.default_roles.tenant.admin'),
            ])
        ) {
            return redirect(route('dashboard'));
        }

        if ($this->checkPinVerified($student_id)) {
            return $this->redirectAfterPinVerification();
        }

        $data['student'] = $this->user->findUser($student_id);

        return view('pages.pins.enter', $data);
    }

    /**
     * Verify a PIN entered by a user.
     */
    public function verify(PinVerify $req, int $student_id): RedirectResponse
    {
        $user = auth()->user();
        $pin = $this->pin->getValidPinByCode($req->pin_code)->first()
            ?? $this->pin->getUserPinBuilder($req->pin_code, $user->id, $student_id)->first();

        // If PIN exists and has been used less than 6 times
        if ($pin && $pin->usage_count < 6) {
            $this->pin->updatePin($pin->id, [
                'usage_count' => $pin->usage_count + 1,
                'user_id' => $user->id,
                'student_id' => $student_id,
                'is_used' => true,
            ]);

            session()->put('pin_verified', $student_id);

            return $this->redirectAfterPinVerification();
        }

        return redirect()->route('pins.enter', Qs::hash($student_id))
            ->with('flash_danger', __('msg.pin_fail'));
    }

    /**
     * Generate and store a batch of random PINs.
     */
    public function store(PinCreate $req): RedirectResponse
    {
        $count = $req->pin_count;
        $pins = [];

        for ($i = 0; $i < $count; $i++) {
            $code = Str::random(5).'-'.Str::random(5).'-'.Str::random(6);
            $pins[] = ['code' => strtoupper($code)];
        }

        $this->pin->insertPins($pins);

        return redirect()->route('pins.index')->with('flash_success', __('msg.pin_create'));
    }

    /**
     * Delete all used pins.
     */
    public function destroy(): RedirectResponse
    {
        $this->pin->deleteUsedPins();

        return back()->with('flash_success', 'Pins Deleted Successfully');
    }

    /**
     * Check if the PIN has already been verified for the student.
     */
    protected function checkPinVerified(int $st_id): bool
    {
        return session()->has('pin_verified') && session()->get('pin_verified') == $st_id;
    }

    /**
     * Redirect the user to the originally requested URL (marks/assessments) or dashboard.
     */
    protected function redirectAfterPinVerification(): RedirectResponse
    {
        if (session()->has('marks_url')) {
            return redirect(session()->get('marks_url'));
        }
        if (session()->has('assessments_url')) {
            return redirect(session()->get('assessments_url'));
        }

        return redirect()->route('dashboard');
    }
}
