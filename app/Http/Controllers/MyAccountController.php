<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Helpers\Usr;
use App\Http\Requests\UserChangePass;
use App\Http\Requests\UserProfileUpdate;
use App\Repositories\UserRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class MyAccountController extends Controller
{
    protected $user;

    public function __construct(UserRepo $user)
    {
        $this->user = $user;
    }

    public function edit_profile()
    {
        $d['my'] = $user = auth()->user();
        $where = ['user_id' => $user->id];
        $d['staff_rec'] = $this->user->getStaffRecord($where)->first();

        return view('pages.my_account', $d);
    }

    public function update_profile(UserProfileUpdate $req)
    {
        $user = auth()->user();

        if (!$user->username && !$req->username && !$req->email)
            return back()->with('pop_error', __('msg.user_invalid'));

        $except = ['_token', '_method'];
        $d = $user->username ? $req->except([...Qs::getStaffRecord(['username']), ...$except]) : $req->except([...Qs::getStaffRecord(), ...$except]);

        $user_role = $user->role;
        $code = $user->code;

        if ($req->hasFile('photo')) {
            $photo = $req->file('photo');
            $f = Qs::getFileMetaData($photo);
            $f['name'] = 'photo.' . $f['ext'];

            if (Usr::tenancyInitilized()) {
                $f['path'] = $d['photo'] = Qs::getUploadPath($user_role) . '/' . $code . '/' . $f['name'];
                $photo->storeAs($f['path']);
            } else {
                $f['path'] = $photo->storeAs(Qs::getUploadPath($user_role) . $code, $f['name']);
                $d['photo'] = 'storage/' . $f['path'];
            }
        }

        // If email changes on update; must verify again.
        if ($user->email !== $d['email'])
            $d['email_verified_at'] = NULL;

        $user->update($d);
        $staff_data_edit = $this->user->whereStaffRecord(['user_id' => $user->id])->first()->staff_data_edit;

        if ($staff_data_edit == true || $user->hasRole(config('roles.default_roles.central.root'))) {
            $d2 = $req->only(Qs::getStaffRecord());
            $this->user->updateStaffRecord(['user_id' => $user->id], $d2);
        }

        return back()->with('flash_success', __('msg.update_ok'));
    }

    public function change_pass(UserChangePass $req)
    {
        $user = auth()->user();
        $current_pass = $user->password;
        $old_pass = $req->current_password;
        $new_pass = $req->new_password;

        if (password_verify($old_pass, $current_pass)) {
            $data['password'] = Hash::make($new_pass);
            $data['password_updated_at'] = Carbon::now();
            $user->update($data);

            return back()->with('flash_success', __('msg.p_changed'));
        }

        return back()->with('flash_danger', __('msg.p_reset_fail'));
    }

    public function update_preferences(Request $request)
    {
        $user = auth()->user();

        $user->update([
            'preferences' => [
                'always_minimize_sidebar' => $request->boolean('always_minimize_sidebar'),
                'allow_system_sounds' => $request->boolean('allow_system_sounds'),
                'active_system_skin' => $request->active_system_skin,
            ],
        ]);

        return back()->with('flash_success', __('msg.update_ok'));
    }

    public function update_hidden_alerts(Request $request)
    {
        $user = auth()->user();

        $data = ['hidden_alert_ids' => $request->input('hidden_alert_ids')];
        $user->update($data);

        return Qs::jsonUpdateOk();
    }

    public function clear_hidden_alerts()
    {
        $user = auth()->user();

        $data = ['hidden_alert_ids' => []];
        $user->update($data);

        return Qs::jsonUpdateOk();
    }
}
