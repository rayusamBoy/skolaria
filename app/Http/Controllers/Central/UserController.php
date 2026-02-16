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
use Illuminate\Support\Str;

class UserController extends Controller
{
    protected $user, $loc, $my_class, $permission;

    public function __construct(UserRepo $user, LocationRepo $loc, MyClassRepo $my_class, PermissionRepo $permission)
    {
        $this->permissionMap = config('permissions.names.central.central_users');
        parent::__construct();
        $this->user = $user;
        $this->loc = $loc;
        $this->my_class = $my_class;
        $this->permission = $permission;
    }

    public function index()
    {
        $d['nationals'] = $this->loc->getAllNationals();
        $d['blood_groups'] = $this->user->getBloodGroups();
        $d['roles'] = $this->permission->getRoles();
        $d['permissions'] = $this->permission->getPermissions();

        return view('pages.central.users.index', $d);
    }

    public function edit($id)
    {
        $id = Usr::decodeHash($id);

        $except_role = config('roles.default_roles.tenant.student');
        $except_roles = (array) Usr::userIsHead() ? [$except_role] : [$except_role, config('roles.default_roles.tenant.superadmin')];
        $d['roles'] = $roles = $this->permission->getRoles($except_roles);
        $d['permissions'] = $this->permission->getPermissions();
        $d['user'] = $this->user->find($id);
        $d['staff_rec'] = $this->user->getStaffRecord(['user_id' => $id])->first();
        $d['users'] = $this->user->getUsersByRoleNames($roles->pluck('name')->toArray());
        $d['blood_groups'] = $this->user->getBloodGroups();
        $d['nationals'] = $this->loc->getAllNationals();

        return view('pages.central.users.edit', $d);
    }

    public function reset_pass($id)
    {
        $id = Usr::decodeHash($id);

        // Redirect if Making Changes to Head
        if (Usr::userIsHead($id) && !Usr::userIsHead())
            return back()->with('flash_danger', __('msg.denied'));

        $data['password'] = Hash::make('user');
        $data['password_updated_at'] = null;
        $this->user->update($id, $data);

        return back()->with('flash_success', __('msg.pu_reset'));
    }

    public function store(UserRequest $req)
    {
        $role_names = $req->roles;
        $except = ['_token', '_method'];
        $data = $req->except([...Usr::getStaffRecord(), ...Usr::getParentRelativeRecord(), ...$except]);
        $emp_date = $req->emp_date ?? now();
        $staff_id = Usr::getAppCode() . '/STAFF/' . date('Y/m', strtotime($emp_date)) . '/' . mt_rand(1000, 9999);
        $last_role_name = strtolower(last($role_names));
        $pass = $req->password ?: $last_role_name;

        $data['name'] = $name = ucwords(strtolower($req->name));
        $data['code'] = $code = strtoupper(Str::random(10));
        $data['dob'] = $req->dob;
        $data['username'] = $uname = $req->username ?? $staff_id;
        $data['password'] = Hash::make($pass);
        $data['message_media_heading_color'] = Usr::getUserMediaHeadingColor($name); // Use a unique text color based on the name

        if ($req->hasFile('photo')) {
            $photo = $req->file('photo');
            $f = Usr::getFileMetaData($photo);
            $f['name'] = 'photo.' . $f['ext'];
            $f['path'] = $photo->storeAs(Usr::getUploadPath($last_role_name) . $data['code'], $f['name']);
            $data['photo'] = 'storage/' . $f['path'];
        } else {
            $data['photo'] = "storage/" . Usr::createAvatar($name, $code, $last_role_name);
        }

        /* Ensure that both username and Email are not blank */
        if (!$uname && !$req->email)
            return Usr::json(__('msg.user_invalid'), FALSE);

        $user = $this->user->create($data); // Create User
        $user->syncRoles($role_names);

        if ($req->has('permissions') AND $req->filled('permissions')) {
            $user->syncPermissions($req->permissions);
        }

        /* Create staff record */
        $d2 = $req->only(Usr::getStaffRecord());
        $d2['user_id'] = $user->id;
        $d2['code'] = $staff_id;
        $d2['subjects_studied'] = isset($d2['subjects_studied']) ? json_encode(explode(",", $d2['subjects_studied'])) : null;

        $this->user->createStaffRecord($d2);

        return Usr::jsonStoreOk();
    }

    public function update(UserRequest $req, $id)
    {
        $id = (int) Usr::decodeHash($id);

        // Redirect if Making Changes to Head
        if (Usr::userIsHead($id) && !Usr::userIsHead())
            return Usr::json(__('msg.denied'), false);

        $user = $this->user->find($id);
        $except = [...Usr::getStaffRecord(), '_token', '_method', 'roles'];
        $data = $req->except($except);
        $role_names = $req->roles;
        $last_role_name = strtolower(last($role_names));

        $data['name'] = $name = ucwords(strtolower($req->name));
        $data['message_media_heading_color'] = Usr::getUserMediaHeadingColor($name); // Use a unique text color based on the name

        if ($req->hasFile('photo')) {
            $photo = $req->file('photo');
            $f = Usr::getFileMetaData($photo);
            $f['name'] = 'photo.' . $f['ext'];
            $f['path'] = $photo->storeAs(Usr::getUploadPath($last_role_name) . $user->code, $f['name']);
            $data['photo'] = 'storage/' . $f['path'];
        }

        $this->user->update($id, $data);   /* Update user record */
        $user->syncRoles($req->roles);
        if ($req->has('permissions') AND $req->filled('permissions')) {
            $user->syncPermissions($req->permissions);
        }

        /* Update staff record */
        $d2 = $req->only(Usr::getStaffRecord());
        $d2['code'] = $user->code;
        $d2['subjects_studied'] = isset($d2['subjects_studied']) ? json_encode(explode(",", $d2['subjects_studied'])) : null;
        $this->user->updateStaffRecord(['user_id' => $id], $d2);

        return Usr::jsonUpdateOk();
    }

    public function show($user_id)
    {
        $user_id = Usr::decodeHash($user_id);
        if (!$user_id)
            return back();

        $data['user'] = $this->user->find($user_id);
        $data['staff_rec'] = $this->user->getStaffRecord(where: ['user_id' => $user_id])->first() ?: null;

        return view('pages.central.users.show', $data);
    }

    public function destroy($id)
    {
        $id = Usr::decodeHash($id);
        $user = $this->user->find($id);

        $this->user->forceDelete($user->id);
        // Delete user's photo(s) from storage if exists
        Usr::deleteUserPhotoDirs($user->roles->pluck('name'), $user->code);

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
