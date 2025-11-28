<?php

namespace App\Http\Controllers;

use App\Helpers\Usr;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserBlockedState;
use App\Http\Requests\UserRequest;
use App\Http\Requests\UserStaffDataEditState;
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
        $d['nationals'] = $this->loc->getAllNationals();
        $d['blood_groups'] = $this->user->getBloodGroups();
        $d['roles'] = $this->permission->getRoles();
        $d['permissions'] = $this->permission->getPermissions();

        return view('pages.users.index', $d);
    }


    public function edit($id)
    {
        $id = Usr::decodeHash($id);

        $d['roles'] = $roles = $this->permission->getRoles();
        $d['permissions'] = $this->permission->getPermissions();
        $d['user'] = $this->user->find($id);
        $d['staff_rec'] = $this->user->getStaffRecord(['user_id' => $id])->first();
        $d['users'] = $this->user->getUsersByRoleNames($roles->pluck('name')->toArray());
        $d['blood_groups'] = $this->user->getBloodGroups();
        $d['nationals'] = $this->loc->getAllNationals();

        return view('pages.users.edit', $d);
    }

    public function reset_pass($id)
    {
        // Redirect if Making Changes to Head of Super Admins
        if (Usr::headSA($id) && !Usr::userIsHead())
            return back()->with('flash_danger', __('msg.denied'));

        $data['password'] = Hash::make('user');
        $data['password_updated_at'] = NULL;

        $this->user->update($id, $data);

        return back()->with('flash_success', __('msg.pu_reset'));
    }

    public function store(UserRequest $req)
    {
        $except = ['_token', '_method'];
        $user_role = $req->user_role;
        $data = $req->except([...Usr::getStaffRecord(), ...Usr::getParentRelativeRecord(), ...$except]);

        $data['name'] = $name = ucwords(strtolower($req->name));
        $data['user_role'] = $user_role;
        $data['code'] = $code = strtoupper(Str::random(10));
        $data['photo'] = Usr::createAvatar($name, $code, $user_role);
        $data['dob'] = $req->dob;

        $user_is_staff = \in_array($user_role, Usr::getStaff());
        $user_is_teamSA = \in_array($user_role, Usr::getTeamSA());
        $user_is_parent = \in_array($user_role, Usr::getParent());

        $emp_date = $req->emp_date ?: now();
        $staff_id = Usr::getAppCode() . '/STAFF/' . date('Y/m', strtotime($emp_date)) . '/' . mt_rand(1000, 9999);
        $data['username'] = $uname = $user_is_teamSA ? $req->username : $staff_id;

        // If user a parent get the work he/she does
        $data['work'] = $user_is_parent ? $req->work : NULL;

        $pass = $req->password ?: $user_role;
        $data['password'] = Hash::make($pass);
        $data['message_media_heading_color'] = Usr::getUserMediaHeadingColor($name); // Use a unique text color based on the name

        if ($req->hasFile('photo')) {
            $photo = $req->file('photo');
            $f = Usr::getFileMetaData($photo);
            $f['name'] = 'photo.' . $f['ext'];
            $f['path'] = $data['photo'] = Usr::getUploadPath($user_role) . $data['code'] . '/' . $f['name'];
            $photo->storeAs($f['path']);
        }

        /* Ensure that both username and Email are not blank*/
        if (!$uname && !$req->email)
            return Usr::json(__('msg.user_invalid'), FALSE);

        $user = $this->user->create($data); // Create User

        /* CREATE STAFF RECORD */
        if ($user_is_staff) {
            $d2 = $req->only(Usr::getStaffRecord());
            $d2['user_id'] = $user->id;
            $d2['code'] = $staff_id;
            $d2['subjects_studied'] = json_encode(explode(",", $d2['subjects_studied']));

            $this->user->createStaffRecord($d2);
        }

        /* CREATE PARENT RELATIVE RECORD */
        if ($user_is_parent) {
            $d3 = $req->only(Usr::getParentRelativeRecord(['name2']));
            $d3['user_id'] = $user->id;
            // Use name2 from the request (removed above) as name
            $d3['name'] = ucwords(strtolower($req->name2));
            $d3['relation'] = $req->relation;

            $this->user->createParentRelativeRecord($d3);
        }

        return Usr::jsonStoreOk();
    }

    public function update(UserRequest $req, $id)
    {
        $id = (int) Usr::decodeHash($id);

        // Redirect if Making Changes to Head of Super Admins
        if (Usr::headSA($id) && !Usr::headSA(auth()->id()))
            return Usr::json(__('msg.denied'), FALSE);
        elseif (Usr::headSA(auth()->id()) && !Usr::userIsHead())
            return Usr::json(__('msg.denied'), FALSE);

        $user = $this->user->find($id);

        $user_role = $user->role;
        $user_was_staff = \in_array($user->role, Usr::getStaff());
        $user_is_staff = \in_array($user_role, Usr::getStaff());
        $user_is_parent = $user->hasRole('parent');

        $except = [...Usr::getStaffRecord(), ...Usr::getParentRelativeRecord(), '_token', '_method', 'user_roles'];
        $data = $req->except($except);
        $user->syncRoles($req->user_roles);

        $data['name'] = $name = ucwords(strtolower($req->name));
        $data['user_role'] = $user_role;
        $data['work'] = $user_is_parent ? $req->work : NULL;
        $data['message_media_heading_color'] = Usr::getUserMediaHeadingColor($name); // Use a unique text color based on the name

        if ($req->hasFile('photo')) {
            $photo = $req->file('photo');
            $f = Usr::getFileMetaData($photo);
            $f['name'] = 'photo.' . $f['ext'];
            $f['path'] = $data['photo'] = Usr::getUploadPath($user_role) . $user->code . '/' . $f['name'];
            $photo->storeAs($f['path']);
        }

        $this->user->update($id, $data);   /* UPDATE USER RECORD */

        /* UPDATE OR CREATE NEW STAFF RECORD */
        if ($user_was_staff) {
            if ($user_is_staff) {
                $d2 = $req->only(Usr::getStaffRecord());
                $d2['code'] = $user->code;
                $d2['subjects_studied'] = json_encode(explode(",", $d2['subjects_studied']));
                $this->user->updateStaffRecord(['user_id' => $id], $d2);
            } else
                $this->user->deleteStaffRecord(['user_id' => $id]);
        } elseif ($user_is_staff) {
            $d2 = $req->only(Usr::getStaffRecord());
            $d2['code'] = $user->code;
            $d2['subjects_studied'] = json_encode(explode(",", $d2['subjects_studied']));
            $this->user->updateStaffRecord(['user_id' => $id], $d2);
        }

        /* UPDATE PARENT RELATIVE RECORD */
        if ($user_is_parent) {
            $d3 = $req->only(Usr::getParentRelativeRecord(['user_id', 'name2']));
            $d3['name'] = ucwords(strtolower($req->name2));
            $d3['relation'] = $req->relation;
            $this->user->updateParentRelativeRec(['user_id' => $id], $d3);
        }

        return Usr::jsonUpdateOk();
    }

    public function show($user_id)
    {
        $user_id = Usr::decodeHash($user_id);
        if (!$user_id)
            return back();

        $data['user'] = $this->user->find($user_id);

        /* Prevent other Users from viewing Profile of others */
        //  if (auth()->id() != $user_id && !Usr::userIsTeamSA() && !Usr::userIsMyChild(auth()->id(), $user_id))
        //        return redirect(route('dashboard'))->with('pop_error', __('msg.denied'));

        // if (Usr::userIsTeamSATCL())
        //     $data['staff_rec'] = $this->user->getStaffRecord(['user_id' => $user_id])->first() ?: null;

        return view('pages.users.show', $data);
    }

    public function destroy($id)
    {
        $id = Usr::decodeHash($id);

        // Redirect if Making Changes to Heads
        if (Usr::headSA($id))
            return back()->with('pop_error', __('msg.denied'));

        $user = $this->user->find($id);

        if ($user->hasRole('Teacher') && $this->userTeachesSubject($user))
            return back()->with('pop_error', __('msg.del_teacher'));

        $path = Usr::getUploadPath($user->role) . $user->code;
        Storage::exists($path) ? Storage::deleteDirectory($path) : true;
        $this->user->delete($user->id);

        return back()->with('flash_success', __('msg.del_ok'));
    }

    protected function userTeachesSubject($user)
    {
        $subjects = $this->my_class->findSubjectRecByTeacher($user->id);

        return $subjects->isNotEmpty();
    }

    public function update_staff_data_edit_state(UserStaffDataEditState $req)
    {
        $where = ['user_id' => $req->id];
        $data = $req->only("staff_data_edit");
        $this->user->updateStaffRecord($where, $data);

        return Usr::json("ok");
    }

    public function update_user_blocked_state(UserBlockedState $req)
    {
        $user_id = $req->id;
        $data = $req->only("blocked");
        $this->user->update($user_id, $data);

        return Usr::json("ok");
    }
}
