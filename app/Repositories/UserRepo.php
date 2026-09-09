<?php

namespace App\Repositories;

use App\Models\BloodGroup;
use App\Models\Notification;
use App\Models\ParentRelative;
use App\Models\StaffRecord;
use App\Models\UserRegistrationInvite;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Role;

class UserRepo
{
    /*********** User ***************/

    /**
     * Update users matching a column value.
     *
     * @param int    $value   The value to match
     * @param array  $data    Columns to update
     * @param string $column  Column to filter by (default 'id')
     * @return int            Number of updated rows
     */
    public function updateUsersByColumn(int $value, array $data, string $column = 'id'): int
    {
        return $this->getUsersQuery([$column => $value])->update($data);
    }

    /**
     * Update multiple users by their IDs.
     *
     * @param mixed $ids   Array or collection of user IDs
     * @param array $data  Columns to update
     * @return int
     */
    public function updateByIds(mixed $ids, array $data): int
    {
        return User::whereIn('id', $ids)->update($data);
    }

    /**
     * Restore a soft‑deleted user by ID.
     *
     * @param int $id
     * @return int
     */
    public function restoreUser(int $id): int
    {
        return $this->getUsersQuery(['id' => $id])->restore();
    }

    /**
     * Permanently delete a user.
     *
     * @param int $id
     * @return int
     */
    public function forceDeleteUser(int $id): int
    {
        return $this->getUsersQuery(['id' => $id])->forceDelete();
    }

    /**
     * Permanently delete all soft-deleted users.
     */
    public function forceDeleteOnlyTrashed()
    {
        return User::onlyTrashed()->forceDelete();
    }

    /**
     * Create a new user.
     *
     * @param array $data
     * @return User
     */
    public function createUser(array $data): User
    {
        return User::create($data);
    }

    /**
     * Find a user by attributes or create one.
     *
     * @param array $data
     * @return User
     */
    public function firstOrCreate(array $data): User
    {
        return User::firstOrCreate($data);
    }

    /**
     * Update an existing user or create a new one.
     *
     * @param array $where
     * @param array $data
     * @return User
     */
    public function updateOrCreate(array $where, array $data): User
    {
        return User::updateOrCreate($where, $data);
    }

    /**
     * Get the first user matching attributes, or return a new instance.
     *
     * @param array $data
     * @return User
     */
    public function firstOrNew(array $data): User
    {
        return User::firstOrNew($data);
    }

    /**
     * Find a user by primary key.
     *
     * @param int $id
     * @return User|null
     */
    public function findUser(int $id): ?User
    {
        return User::find($id);
    }

    /**
     * Find a soft‑deleted user by ID.
     *
     * @param int $id
     * @return User|null
     */
    public function findOnlyTrashed(int $id): ?User
    {
        return User::onlyTrashed()->where('id', $id)->first();
    }

    /**
     * Get all users, optionally with eager‑loaded relations.
     *
     * @param array $with
     * @return Collection
     */
    public function getAllUsers(array $with = []): Collection
    {
        return $with ? User::with($with)->get() : User::all();
    }

    /**
     * Get users having any of the given role names, ordered by name.
     *
     * @param array $role_names
     * @param array $with
     * @return Collection
     */
    public function getUsersByRoleNames(array $role_names, array $with = []): Collection
    {
        return User::role($role_names)->with($with)->orderBy('name', 'asc')->get();
    }

    /**
     * Get all soft‑deleted users with their roles.
     *
     * @return Collection
     */
    public function getTrashedUsers(): Collection
    {
        return User::with('roles')->onlyTrashed()->get();
    }

    /**
     * Query builder for users matching conditions.
     *
     * @param array $where
     * @return Builder|User
     */
    public function getUsersQuery(array $where): Builder|User
    {
        return User::where($where);
    }

    /********** STAFF RECORD **********/

    /**
     * Query builder for staff records matching conditions.
     *
     * @param array $where
     * @return Builder
     */
    public function whereStaffRecord(array $where): Builder|StaffRecord
    {
        return StaffRecord::where($where);
    }

    /**
     * Create a new staff record.
     *
     * @param array $data
     * @return StaffRecord
     */
    public function createStaffRecordsQuery(array $data): StaffRecord
    {
        return StaffRecord::create($data);
    }

    /**
     * Update staff records matching conditions.
     *
     * @param array $where
     * @param array $data
     * @return int
     */
    public function updateStaffRecord(array $where, array $data): int
    {
        return StaffRecord::where($where)->update($data);
    }

    /**
     * Get staff records matching conditions, with user and LGA.
     *
     * @param array $where
     * @return Collection
     */
    public function getStaffRecordsByConditions(array $where): Collection
    {
        return StaffRecord::where($where)->with(['user', 'lga'])->get();
    }

    /**
     * Delete staff records matching conditions.
     *
     * @param array $where
     * @return int
     */
    public function deleteStaffRecords(array $where): int
    {
        return StaffRecord::where($where)->delete();
    }

    /********** BLOOD GROUPS **********/

    /**
     * Get all blood groups ordered by name.
     *
     * @return Collection
     */
    public function getBloodGroups(): Collection
    {
        return BloodGroup::orderBy('name')->get();
    }

    /********** PARENT RELATIVE **********/

    /**
     * Create a new parent/relative record.
     *
     * @param array $data
     * @return ParentRelative
     */
    public function createParentRelativeRecord(array $data): ParentRelative
    {
        return ParentRelative::create($data);
    }

    /**
     * Update or create a parent/relative record.
     *
     * @param array $attributes
     * @param array $values
     * @return ParentRelative
     */
    public function updateOrCreateParentRelative(array $attributes, array $values): ParentRelative
    {
        return ParentRelative::updateOrCreate($attributes, $values);
    }

    /********** NOTIFICATIONS **********/

    /**
     * Delete a notification by primary key.
     *
     * @param mixed $id
     * @return bool|null
     */
    public function deleteNotification(mixed $id): ?bool
    {
        return Notification::find($id)?->delete();
    }

    /********** ROLES **********/

    /**
     * Get roles optionally excluding some by name.
     *
     * @param array $except
     * @return Collection
     */
    public function getRolesByName(array $except = []): Collection
    {
        return Role::whereNotIn('name', $except)->get();
    }

    /********** INVITATIONS **********/

    /**
     * Get all user registration invitations with their creators.
     *
     * @return Collection
     */
    public function getInvitations(): Collection
    {
        return UserRegistrationInvite::with('creator')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Create a new user registration invitation.
     *
     * @param array $data
     * @return UserRegistrationInvite
     */
    public function createInvitation(array $data): UserRegistrationInvite
    {
        return UserRegistrationInvite::create($data);
    }

    /**
     * Find an invitation by ID or fail.
     *
     * @param int $id
     * @return UserRegistrationInvite
     */
    public function findInvitationOrFail(int $id): UserRegistrationInvite
    {
        return UserRegistrationInvite::findOrFail($id);
    }

    /**
     * Query builder for invitations matching conditions.
     * @param array $where
     * @return Builder|UserRegistrationInvite
     */
    public function whereInvitationBuilder(array $where): Builder|UserRegistrationInvite
    {
        return UserRegistrationInvite::where($where);
    }
}
