<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Helpers\Usr;
use App\Http\Requests\UserChangePass;
use App\Http\Requests\UserProfileUpdate;
use App\Repositories\UserRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use PermissionKit\Attributes\SkipPermission;

class MyAccountController extends Controller
{
    public function __construct(protected UserRepo $user) {}

    /**
     * Show the profile edit form.
     */
    public function edit_profile(): View
    {
        $current_user = auth()->user();

        $data['my'] = $current_user;
        $data['staff_rec'] = $this->user->getStaffRecordsByConditions(['user_id' => $current_user->id])->first();

        return view('pages.my_account', $data);
    }

    /**
     * Update the authenticated user's profile.
     */
    public function update_profile(UserProfileUpdate $req): RedirectResponse
    {
        $current_user = auth()->user();

        if (! $current_user->username && ! $req->username && ! $req->email) {
            return back()->with('pop_error', __('msg.user_invalid'));
        }

        $except = ['_token', '_method'];
        $staff_fields = Qs::getStaffRecordFields($current_user->username ? ['username'] : []);
        $profile_data = $req->except([...$staff_fields, ...$except]);

        $user_role = $current_user->role;
        $user_code = $current_user->code;

        if ($req->hasFile('photo')) {
            $photo = $req->file('photo');
            $file_meta = Qs::getFileMetaData($photo);
            $file_meta['name'] = 'photo.'.$file_meta['ext'];

            if (Usr::tenancyInitialized()) {
                $file_meta['path'] = $profile_data['photo_path'] = Qs::getUploadPath($user_role).'/'.$user_code.'/'.$file_meta['name'];
                $photo->storeAs($file_meta['path']);
            } else {
                $file_meta['path'] = $photo->storeAs(Qs::getUploadPath($user_role).$user_code, $file_meta['name']);
                $profile_data['photo_path'] = 'storage/'.$file_meta['path'];
            }
        }

        // Must re‑verify email if it changed
        if ($current_user->email !== $profile_data['email']) {
            $profile_data['email_verified_at'] = null;
        }

        $current_user->update($profile_data);

        $staff_record = $this->user->whereStaffRecord(['user_id' => $current_user->id])->first();
        $allow_staff_edit = $staff_record->allow_staff_data_edit ?? false;

        if ($allow_staff_edit || $current_user->hasAnyRole(Usr::getTenancyAwareRootRoles())) {
            $staff_data = $req->only(Qs::getStaffRecordFields());
            $this->user->updateStaffRecord(['user_id' => $current_user->id], $staff_data);
        }

        return back()->with('flash_success', __('msg.update_ok'));
    }

    /**
     * Change the authenticated user's password.
     */
    public function change_pass(UserChangePass $req): RedirectResponse
    {
        $current_user = auth()->user();
        $current_password = $current_user->password;
        $current_password_input = $req->current_password;
        $new_password = $req->password;

        if (password_verify($current_password_input, $current_password)) {
            $current_user->update([
                'password' => Hash::make($new_password),
                'password_updated_at' => Carbon::now(),
            ]);

            return back()->with('flash_success', __('msg.p_changed'));
        }

        return back()->with('flash_danger', __('msg.p_reset_fail'));
    }

    /**
     * Update user interface preferences.
     */
    public function update_preferences(Request $request): RedirectResponse
    {
        $allow_offline = $request->boolean('allow_offline_access');
        $previous = auth()->user()->preferences['allow_offline_access'] ?? 0;

        auth()->user()->update([
            'preferences' => [
                'always_minimize_sidebar' => $request->boolean('always_minimize_sidebar'),
                'allow_system_sounds' => $request->boolean('allow_system_sounds'),
                'active_system_skin' => $request->active_system_skin,
                'allow_offline_access' => $allow_offline ? 1 : 0,
            ],
        ]);

        // If the user just turned it off, signal the client to purge caches.
        if (! $allow_offline && $previous) {
            session()->flash('purge_offline_caches', true);
        }

        return back()->with('flash_success', __('msg.update_ok'));
    }

    /**
     * Update hidden alert IDs (AJAX).
     */
    #[SkipPermission]
    public function update_hidden_alerts(Request $request): JsonResponse
    {
        auth()->user()->update(['hidden_alert_ids' => $request->input('hidden_alert_ids')]);

        return Qs::jsonUpdateOk();
    }

    /**
     * Clear all hidden alert IDs (AJAX).
     */
    #[SkipPermission]
    public function clear_hidden_alerts(): JsonResponse
    {
        auth()->user()->update(['hidden_alert_ids' => []]);

        return Qs::jsonUpdateOk();
    }
}
