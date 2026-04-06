<?php

namespace App\Repositories;

use App\Models\BloodGroup;
use App\Models\Notification;
use App\Models\ParentRelative;
use App\Models\StaffRecord;
use App\User;
use Spatie\Permission\Models\Role;

class UserRepo
{
    /*********** User ***************/

    public function getIds()
    {
        return User::pluck('id');
    }

    public function update(int $value, array $data, $column = 'id')
    {
        return $this->where([$column => $value])->update($data);
    }

    public function updateByIds(mixed $ids, array $data)
    {
        return User::whereIn('id', $ids)->update($data);
    }

    public function delete(int $id)
    {
        return User::destroy($id);
    }

    public function restore(int $id)
    {
        return $this->where(['id' => $id])->restore();
    }

    public function forceDelete(int $id)
    {
        return $this->where(['id' => $id])->forceDelete();
    }

    public function get(array $where)
    {
        return $this->where($where)->get();
    }

    public function create(array $data)
    {
        return User::create($data);
    }

    public function firstOrCreate(array $data)
    {
        return User::firstOrCreate($data);
    }

    public function updateOrCreate(array $where, array $data)
    {
        return User::updateOrCreate($where, $data);
    }

    public function firstOrNew(array $data)
    {
        return User::firstOrNew($data);
    }

    public function find(int $id)
    {
        return User::find($id);
    }

    public function findOnlyTrashed(int $id)
    {
        return User::onlyTrashed()->where('id', $id)->first();
    }

    public function all()
    {
        return User::all();
    }

    public function getUsersByRoleNames(array $role_names)
    {
        return User::role($role_names)->orderBy('name', 'asc')->get();
    }

    public function getTrashed()
    {
        return User::onlyTrashed()->get();
    }

    public function where(array $where)
    {
        return User::where($where);
    }

    /********** STAFF RECORD ********/

    public function whereStaffRecord($where)
    {
        return StaffRecord::where($where);
    }

    public function createStaffRecord(array $data)
    {
        return StaffRecord::create($data);
    }

    public function updateStaffRecord(array $where, array $data)
    {
        return StaffRecord::where($where)->update($data);
    }

    public function getStaffRecord(array $where)
    {
        return StaffRecord::where($where)->with(['user', 'lga'])->get();
    }

    public function deleteStaffRecord(array $where)
    {
        return StaffRecord::where($where)->delete();
    }

    /********** BLOOD GROUPS ********/

    public function getBloodGroups()
    {
        return BloodGroup::orderBy('name')->get();
    }

    /********** PARENT CLOSE RELATIVE ********/

    public function createParentRelativeRecord($data)
    {
        return ParentRelative::create($data);
    }

    public function updateParentRelativeRec($where, $data)
    {
        return ParentRelative::where($where)->update($data);
    }

    /********** Notifications ********/

    public function deleteNotification(mixed $id)
    {
        return Notification::find($id)->delete();
    }

    public function getNotification(array $where)
    {
        return Notification::where($where)->get();
    }

    /********** Roles ********/
    public function getRoles($except = [])
    {
        $roles = Role::whereNotIn('name', $except)->get();
        return $roles;
    }

    public function findRoleByName(string $role_name)
    {
        return Role::where('name', $role_name)->with('users')->first();
    }
}
