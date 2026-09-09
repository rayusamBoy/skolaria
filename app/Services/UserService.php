<?php

namespace App\Services;

use App\Helpers\Usr;
use App\Repositories\LocationRepo;
use App\Repositories\MyClassRepo;
use App\Repositories\PermissionRepo;
use App\Repositories\StudentRepo;
use App\Repositories\UserRepo;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserService
{
    protected UserRepo $user;

    protected StudentRepo $student;

    protected LocationRepo $loc;

    protected MyClassRepo $my_class;

    protected PermissionRepo $permission;

    public function __construct(
        UserRepo $user,
        StudentRepo $student,
        LocationRepo $loc,
        MyClassRepo $my_class,
        PermissionRepo $permission
    ) {
        $this->user = $user;
        $this->student = $student;
        $this->loc = $loc;
        $this->my_class = $my_class;
        $this->permission = $permission;
    }

    /**
     * Create a new user with roles, staff record, and parent record if applicable.
     *
     * @param  array  $data  Validated user attributes
     * @param  array  $roles  Role names
     * @param  array|null  $permissions  Additional permissions
     */
    public function storeUser(array $data, array $roles, ?array $permissions = null): User
    {
        $last_role_name = strtolower(last($roles));
        $user_is_staff = $this->hasStaffRole($roles);

        $user_data = $this->prepareUserData($data, $last_role_name, $user_is_staff);
        $user_data['password'] = Hash::make($data['password'] ?? $last_role_name);

        if (empty($user_data['username']) && empty($data['email'])) {
            throw new \InvalidArgumentException(__('msg.user_invalid'));
        }

        $user = $this->user->createUser($user_data);
        $user->syncRoles($roles);

        if (! empty($permissions)) {
            $user->syncPermissions($permissions);
        }

        if ($user_is_staff) {
            $this->createStaffRecord($user->id, $data);
        }

        if ($user->hasRole(config('roles.default_roles.tenant.parent'))) {
            $this->createParentRecord($user->id, $data);
        }

        return $user;
    }

    /**
     * Update an existing user.
     */
    public function updateUser(int $user_id, array $data, array $roles, ?array $permissions = null): void
    {
        $user = $this->user->findUser($user_id);
        $last_role_name = strtolower(last($roles));

        $user_was_staff = $this->hasStaffRole($user->roles->pluck('name')->toArray());
        $user_is_staff = $this->hasStaffRole($roles);
        $user_is_parent = $user->hasRole(config('roles.default_roles.tenant.parent'));

        $user_data = $this->prepareUserData($data, $last_role_name, $user_is_staff, $user->code);
        $user_data['occupation'] = $user_is_parent ? ($data['occupation'] ?? null) : null;

        $this->handlePhotoUpload($data, $user_data, $user->code, $last_role_name, false);
        $user->update($user_data);

        $user->syncRoles($roles);
        if ($permissions !== null) {
            $user->syncPermissions($permissions);
        }

        $this->handleStaffRecordTransition($user->id, $user_was_staff, $user_is_staff, $data);
        $this->handleParentRecordUpdate($user->id, $user_is_parent, $data);
    }

    /**
     * Prepare user data array for insertion/update.
     */
    protected function prepareUserData(array $data, string $last_role_name, bool $user_is_staff, ?string $existing_code = null): array
    {
        $user_data = $data;

        $user_data['name'] = ucwords(strtolower($data['name']));
        $user_data['message_media_heading_color'] = Usr::getUserMediaHeadingColor($user_data['name']);
        $user_data['date_of_birth'] = $data['date_of_birth'] ?? null;

        if ($existing_code === null) {
            // New user
            $user_data['code'] = strtoupper(Str::random(10));
            $user_data['username'] = $user_is_staff
                ? $this->generateStaffId($data['employment_date'] ?? null)
                : ($data['username'] ?? null);
            $this->handlePhotoUpload($data, $user_data, $user_data['code'], $last_role_name, true);
        } else {
            $user_data['code'] = $existing_code;
        }

        return $user_data;
    }

    /**
     * Handle photo upload or avatar generation.
     */
    protected function handlePhotoUpload(array $data, array &$user_data, string $user_code, string $last_role_name, bool $is_new): void
    {
        if (! empty($data['photo']) && is_uploaded_file($data['photo']->getRealPath())) {
            $photo = $data['photo'];
            $file_meta = Usr::getFileMetaData($photo);
            $file_meta['name'] = 'photo.'.$file_meta['ext'];
            $file_meta['path'] = Usr::getUploadPath($last_role_name).$user_code.'/'.$file_meta['name'];
            $photo->storeAs($file_meta['path']);
            $user_data['photo_path'] = $file_meta['path'];
        } elseif ($is_new) {
            $user_data['photo_path'] = Usr::createAvatar($user_data['name'], $user_code, $last_role_name);
        }
    }

    /**
     * Generate a unique staff ID.
     */
    protected function generateStaffId($employment_date = null): string
    {
        $date = $employment_date ?: now();

        return Usr::getSystemShortCode().'/STAFF/'.date('Y/m', strtotime($date)).'/'.mt_rand(1000, 9999);
    }

    /**
     * Create staff record.
     */
    protected function createStaffRecord(int $user_id, array $data): void
    {
        $staff_data = $this->extractStaffFields($data);
        $staff_data['user_id'] = $user_id;
        $staff_data['code'] = $this->generateStaffId($data['employment_date'] ?? null);
        $this->user->createStaffRecordsQuery($staff_data);
    }

    /**
     * Create parent relative record.
     */
    protected function createParentRecord(int $user_id, array $data): void
    {
        $parent_data = $this->extractParentFields($data);
        $parent_data['user_id'] = $user_id;
        $parent_data['name'] = ucwords(strtolower($data['relative_name'] ?? ''));
        $parent_data['relation'] = $data['relation'] ?? null;
        $this->user->createParentRelativeRecord($parent_data);
    }

    /**
     * Handle staff record transition during update.
     */
    protected function handleStaffRecordTransition(int $user_id, bool $was_staff, bool $is_staff, array $data): void
    {
        if ($was_staff) {
            if ($is_staff) {
                $staff_data = $this->extractStaffFields($data);
                $this->user->updateStaffRecord(['user_id' => $user_id], $staff_data);
            } else {
                $this->user->deleteStaffRecords(['user_id' => $user_id]);
            }
        } elseif ($is_staff) {
            $staff_data = $this->extractStaffFields($data);
            $staff_data['user_id'] = $user_id;
            $staff_data['code'] = $this->generateStaffId($data['employment_date'] ?? null);
            $this->user->createStaffRecordsQuery($staff_data);
        }
    }

    /**
     * Update parent record or create if missing.
     */
    protected function handleParentRecordUpdate(int $user_id, bool $is_parent, array $data): void
    {
        if (! $is_parent) {
            return;
        }

        $parent_data = $this->extractParentFields($data);
        $parent_data['name'] = ucwords(strtolower($data['relative_name'] ?? ''));
        $parent_data['relation'] = $data['relation'] ?? null;
        $this->user->updateOrCreateParentRelative(['user_id' => $user_id], $parent_data);
    }

    /**
     * Extract staff fields from full data array.
     */
    protected function extractStaffFields(array $data): array
    {
        $staff_fields = Usr::getStaffRecordFields();

        return array_intersect_key($data, array_flip($staff_fields));
    }

    /**
     * Extract parent relative fields from full data array.
     */
    protected function extractParentFields(array $data): array
    {
        $parent_fields = Usr::getParentRelativeRecordFields();
        $parent_fields = array_diff($parent_fields, ['user_id', 'name']); // those set separately

        return array_intersect_key($data, array_flip($parent_fields));
    }

    /**
     * Determine if any role is a staff role.
     */
    protected function hasStaffRole(array $roles): bool
    {
        return \count(array_intersect($roles, Usr::getStaffRoleNames())) > 0;
    }
}
