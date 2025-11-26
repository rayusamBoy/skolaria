<?php

namespace App\Http\Controllers\Central;

use App\Helpers\Usr;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserBlockedState;
use App\Http\Requests\UserRequest;
use App\Repositories\LocationRepo;
use App\Repositories\MyClassRepo;
use App\Repositories\PermissionRepo;
use App\Repositories\UserRepo;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserController extends Controller
{
    protected $user, $loc, $my_class, $permission;

    public function __construct(UserRepo $user, LocationRepo $loc, MyClassRepo $my_class, PermissionRepo $permission)
    {
        $this->user = $user;
        $this->loc = $loc;
        $this->my_class = $my_class;
        $this->permission = $permission;
    }

    public function index()
    {
        $d['users'] = $this->user->getUsersByRoleNames(['Super Admin']);
        $d['nationals'] = $this->loc->getAllNationals();
        $d['blood_groups'] = $this->user->getBloodGroups();
        $d['roles'] = $this->permission->getRoles();

        return view('pages.central.users.index', $d);
    }

    public function edit($id)
    {
        $id = Usr::decodeHash($id);
     
        $d['roles'] = $this->permission->getRoles();
        $d['user'] = $this->user->find($id);
        $d['staff_rec'] = $this->user->getStaffRecord(['user_id' => $id])->first();
        $d['users'] = $this->user->getUsersByRoleNames(['Super Admin']);
        $d['blood_groups'] = $this->user->getBloodGroups();
        $d['nationals'] = $this->loc->getAllNationals();

        return view('pages.central.users.edit', $d);
    }

    public function reset_pass($id)
    {
        $data['password'] = Hash::make('user');
        $data['password_updated_at'] = NULL;
        $this->user->update($id, $data);

        return back()->with('flash_success', __('msg.pu_reset'));
    }

    public function store(UserRequest $req)
    {
        $user_role = $req->user_role;
        $except = ['_token', '_method'];
        $data = $req->except(array_merge(Usr::getStaffRecord(), Usr::getParentRelativeRecord(), $except));

        $data['name'] = $name = ucwords(strtolower($req->name));
        $data['user_role'] = $user_role;
        $data['code'] = $code = strtoupper(Str::random(10));
        $data['photo'] = "storage/" . Usr::createAvatar($name, $code, $user_role);
        $data['dob'] = $req->dob;

        $emp_date = $req->emp_date ?? now();
        $staff_id = Usr::getAppCode() . '/STAFF/' . date('Y/m', strtotime($emp_date)) . '/' . mt_rand(1000, 9999);
        $data['username'] = $uname = $req->username ?? $staff_id;

        $pass = $req->password ?: $user_role;
        $data['password'] = Hash::make($pass);
        $data['message_media_heading_color'] = Usr::getUserMediaHeadingColor($name); // Use a unique text color based on the name

        if ($req->hasFile('photo')) {
            $photo = $req->file('photo');
            $f = Usr::getFileMetaData($photo);
            $f['name'] = 'photo.' . $f['ext'];
            $f['path'] = $photo->storeAs(Usr::getUploadPath($user_role) . $data['code'], $f['name']);
            $data['photo'] = 'storage/' . $f['path'];
        }

        /* Ensure that both username and Email are not blank*/
        if (!$uname && !$req->email)
            return Usr::json(__('msg.user_invalid'), FALSE);

        $user = $this->user->create($data); // Create User

        /* Create staff record */
        $d2 = $req->only(Usr::getStaffRecord());
        $d2['user_id'] = $user->id;
        $d2['code'] = $staff_id;
        $d2['subjects_studied'] = isset($d2['subjects_studied']) ? json_encode(explode(",", $d2['subjects_studied'])) : NULL;

        $this->user->createStaffRecord($d2);

        return Usr::jsonStoreOk();
    }

    public function update(UserRequest $req, $id)
    {
        $id = (int) Usr::decodeHash($id);
        $user = $this->user->find($id);
        $user_type = 'hhh';
        $except = array_merge(Usr::getStaffRecord(), ['_token', '_method', 'user_roles']);
        $data = $req->except($except);

        $data['name'] = $name = ucwords(strtolower($req->name));
        $data['user_type'] = $user_type;
        $data['message_media_heading_color'] = Usr::getUserMediaHeadingColor($name); // Use a unique text color based on the name

        if ($req->hasFile('photo')) {
            $photo = $req->file('photo');
            $f = Usr::getFileMetaData($photo);
            $f['name'] = 'photo.' . $f['ext'];
            $f['path'] = $photo->storeAs(Usr::getUploadPath($user_type) . $user->code, $f['name']);
            $data['photo'] = 'storage/' . $f['path'];
        }

        $this->user->update($id, $data);   /* Update user record */
        $user->syncRoles($req->user_roles);

        /* Update staff record */
        $d2 = $req->only(Usr::getStaffRecord());
        $d2['code'] = $user->code;
        $d2['subjects_studied'] = isset($d2['subjects_studied']) ? json_encode(explode(",", $d2['subjects_studied'])) : NULL;
        $this->user->updateStaffRecord(['user_id' => $id], $d2);

        return Usr::jsonUpdateOk();
    }

    public function show($user_id)
    {
        $user_id = Usr::decodeHash($user_id);
        if (!$user_id)
            return back();

        $data['user'] = $this->user->find($user_id);

        /* Prevent other Users from viewing Profile of others */
        // if (auth()->id() != $user_id && !Usr::userIsHead())
            // return back()->with('pop_error', __('msg.denied'));

        $data['staff_rec'] = $this->user->getStaffRecord(where: ['user_id' => $user_id])->first() ?: null;

        return view('pages.central.users.show', $data);
    }

    public function destroy($id)
    {
        $id = Usr::decodeHash($id);
        $user = $this->user->find($id);
        $path = Usr::getUploadPath($user->user_type) . $user->code;

        Storage::exists($path) ? Storage::deleteDirectory($path) : true;

        $this->user->forceDelete($user->id);

        return back()->with('flash_success', __('msg.del_ok'));
    }

    public function update_user_blocked_state(UserBlockedState $req)
    {
        $user_id = $req->id;
        $data = $req->only("blocked");
        $this->user->update($user_id, $data);

        return Usr::json("ok");
    }
}
