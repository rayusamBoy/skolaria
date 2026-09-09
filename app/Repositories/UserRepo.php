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
     * @param  int  $value  The value to match
     * @param  array  $data  Columns to update
     * @param  string  $column  Column to filter by (default 'id')
     * @return int Number of updated rows
     */
    public function updateUsersByColumn(int $value, array $data, string $column = 'id'): int
    {
        return $this->getUsersQuery([$column => $value])->update($data);
    }

    /**
     * Update multiple users by their IDs.
     *
     * @param  mixed  $ids  Array or collection of user IDs
     * @param  array  $data  Columns to update
     */
    public function updateByIds(mixed $ids, array $data): int
    {
        return User::whereIn('id', $ids)->update($data);
    }

    /**
     * Restore a soft‑deleted user by ID.
     */
    public function restoreUser(int $id): int
    {
        return $this->getUsersQuery(['id' => $id])->restore();
    }

    /**
     * Permanently delete a user.
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
     */
    public function createUser(array $data): User
    {
        return User::create($data);
    }

    /**
     * Find a user by attributes or create one.
     */
    public function firstOrCreate(array $data): User
    {
        return User::firstOrCreate($data);
    }

    /**
     * Update an existing user or create a new one.
     */
    public function updateOrCreate(array $where, array $data): User
    {
        return User::updateOrCreate($where, $data);
    }

    /**
     * Get the first user matching attributes, or return a new instance.
     */
    public function firstOrNew(array $data): User
    {
        return User::firstOrNew($data);
    }

    /**
     * Find a user by primary key.
     */
    public function findUser(int $id): ?User
    {
        return User::find($id);
    }

    /**
     * Find a soft‑deleted user by ID.
     */
    public function findOnlyTrashed(int $id): ?User
    {
        return User::onlyTrashed()->where('id', $id)->first();
    }

    /**
     * Get all users, optionally with eager‑loaded relations.
     */
    public function getAllUsers(array $with = []): Collection
    {
        return $with ? User::with($with)->get() : User::all();
    }

    /**
     * Get users having any of the given role names, ordered by name.
     */
    public function getUsersByRoleNames(array $role_names, array $with = []): Collection
    {
        return User::role($role_names)->with($with)->orderBy('name', 'asc')->get();
    }

    /**
     * Get all soft‑deleted users with their roles.
     */
    public function getTrashedUsers(): Collection
    {
        return User::with('roles')->onlyTrashed()->get();
    }

    /**
     * Query builder for users matching conditions.
     */
    public function getUsersQuery(array $where): Builder|User
    {
        return User::where($where);
    }

    /********** STAFF RECORD **********/

    /**
     * Query builder for staff records matching conditions.
     *
     * @return Builder
     */
    public function whereStaffRecord(array $where): Builder|StaffRecord
    {
        return StaffRecord::where($where);
    }

    /**
     * Create a new staff record.
     */
    public function createStaffRecordsQuery(array $data): StaffRecord
    {
        return StaffRecord::create($data);
    }

    /**
     * Update staff records matching conditions.
     */
    public function updateStaffRecord(array $where, array $data): int
    {
        return StaffRecord::where($where)->update($data);
    }

    /**
     * Get staff records matching conditions, with user and LGA.
     */
    public function getStaffRecordsByConditions(array $where): Collection
    {
        return StaffRecord::where($where)->with(['user', 'lga'])->get();
    }

    /**
     * Delete staff records matching conditions.
     */
    public function deleteStaffRecords(array $where): int
    {
        return StaffRecord::where($where)->delete();
    }

    /********** BLOOD GROUPS **********/

    /**
     * Get all blood groups ordered by name.
     */
    public function getBloodGroups(): Collection
    {
        return BloodGroup::orderBy('name')->get();
    }

    /********** PARENT RELATIVE **********/

    /**
     * Create a new parent/relative record.
     */
    public function createParentRelativeRecord(array $data): ParentRelative
    {
        return ParentRelative::create($data);
    }

    /**
     * Update or create a parent/relative record.
     */
    public function updateOrCreateParentRelative(array $attributes, array $values): ParentRelative
    {
        return ParentRelative::updateOrCreate($attributes, $values);
    }

    /********** NOTIFICATIONS **********/

    /**
     * Delete a notification by primary key.
     */
    public function deleteNotification(mixed $id): ?bool
    {
        return Notification::find($id)?->delete();
    }

    /********** ROLES **********/

    /**
     * Get roles optionally excluding some by name.
     */
    public function getRolesByName(array $except = []): Collection
    {
        return Role::whereNotIn('name', $except)->get();
    }

    /********** INVITATIONS **********/

    /**
     * Get all user registration invitations with their creators.
     */
    public function getInvitations(): Collection
    {
        return UserRegistrationInvite::with('creator')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Create a new user registration invitation.
     */
    public function createInvitation(array $data): UserRegistrationInvite
    {
        return UserRegistrationInvite::create($data);
    }

    /**
     * Find an invitation by ID or fail.
     */
    public function findInvitationOrFail(int $id): UserRegistrationInvite
    {
        return UserRegistrationInvite::findOrFail($id);
    }

    /**
     * Query builder for invitations matching conditions.
     */
    public function whereInvitationBuilder(array $where): Builder|UserRegistrationInvite
    {
        return UserRegistrationInvite::where($where);
    }
}
