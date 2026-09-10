<?php

namespace App\Http\Controllers\Central;

use App\Auth\Attributes\SkipPermission;
use App\Helpers\Usr;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserBlockedState;
use App\Http\Requests\UserRequest;
use App\Repositories\LocationRepo;
use App\Repositories\MyClassRepo;
use App\Repositories\PermissionRepo;
use App\Repositories\UserRepo;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        protected UserRepo $user,
        protected LocationRepo $loc,
        protected MyClassRepo $my_class,
        protected PermissionRepo $permission
    ) {
        $this->permissionMap = config('permissions.names.central.central_users');
        parent::__construct();
    }

    /**
     * Display list of central users.
     */
    public function index(): View
    {
        $data['nationals'] = $this->loc->getAllNationalities();
        $data['blood_groups'] = $this->user->getBloodGroups();
        $data['roles'] = $this->permission->getAllRoles();
        $data['permissions'] = $this->permission->getAllPermissions();

        return view('pages.central.users.index', $data);
    }

    /**
     * Show form to edit a central user.
     */
    public function edit(int $central_user): View
    {
        $except_roles = $this->getExcludedRolesForEditing();

        $data['roles'] = $roles = $this->permission->getAllRoles($except_roles);
        $data['permissions'] = $this->permission->getAllPermissions();
        $data['user'] = $this->user->findUser($central_user);
        $data['staff_rec'] = $this->user->getStaffRecordsByConditions(['user_id' => $central_user])->first();
        $data['users'] = $this->user->getUsersByRoleNames($roles->pluck('name')->toArray());
        $data['blood_groups'] = $this->user->getBloodGroups();
        $data['nationals'] = $this->loc->getAllNationalities();

        return view('pages.central.users.edit', $data);
    }

    /**
     * Reset a user's password to default 'user'.
     */
    public function reset_password(int $user_id): RedirectResponse
    {
        if ($this->cannotModifyHeadUser($user_id)) {
            return back()->with('flash_danger', __('msg.denied'));
        }

        $this->user->updateUsersByColumn($user_id, [
            'password' => Hash::make('user'),
            'password_updated_at' => null,
        ]);

        return back()->with('flash_success', __('msg.pu_reset'));
    }

    /**
     * Store a new central user.
     */
    public function store(UserRequest $req): JsonResponse
    {
        $role_names = $req->roles;
        $last_role_name = strtolower(last($role_names));
        $staff_id = $this->generateStaffId($req->employment_date);
        $username = $req->username ?? $staff_id;

        // Validate username/email presence
        if (! $username && ! $req->email) {
            return Usr::json(__('msg.user_invalid'), false);
        }

        $user_data = $this->prepareUserData($req, $last_role_name, $username, $staff_id);
        $user = $this->user->createUser($user_data);

        // Sync roles and permissions
        $user->syncRoles($role_names);
        $this->syncPermissionsIfProvided($user, $req);

        // Create staff record
        $this->createStaffRecord($user->id, $req, $staff_id);

        return Usr::jsonStoreOk();
    }

    /**
     * Update an existing central user.
     */
    public function update(UserRequest $req, int $user_id): JsonResponse
    {
        if ($this->cannotModifyHeadUser($user_id)) {
            return Usr::json(__('msg.denied'), false);
        }

        $user = $this->user->findUser($user_id);
        $role_names = $req->roles;
        $last_role_name = strtolower(last($role_names));

        $user_data = $this->prepareUserData($req, $last_role_name);
        $this->handlePhotoUpload($req, $user_data, $user->code, $last_role_name);

        $user->update($user_data);
        $user->syncRoles($role_names);
        $this->syncPermissionsIfProvided($user, $req);

        // Update staff record
        $this->updateStaffRecord($user->id, $req);

        return Usr::jsonUpdateOk();
    }

    /**
     * Show own profile.
     */
    #[SkipPermission]
    public function show_profile(int $user_id)
    {
        return $this->show($user_id);
    }

    /**
     * Show details of a specific user.
     */
    public function show(int $user_id): View|RedirectResponse
    {
        if (! $user_id) {
            return back();
        }

        if ($this->isRestrictedView($user_id)) {
            return back()->with('pop_error', __('msg.denied'));
        }

        $data['user'] = $this->user->findUser($user_id);
        $data['staff_rec'] = $this->user->getStaffRecordsByConditions(where: ['user_id' => $user_id])->first() ?: null;

        return view('pages.central.users.show', $data);
    }

    /**
     * Permanently delete a user.
     */
    public function destroy(int $user_id): RedirectResponse
    {
        $user = $this->user->findUser($user_id);
        $this->user->forceDeleteUser($user->id);

        Usr::deleteUserPhotoDirs($user->roles->pluck('name'), $user->code);

        return back()->with('flash_success', __('msg.del_ok'));
    }

    /**
     * Toggle is_blocked status of a user.
     */
    public function toggle_user_block(UserBlockedState $req): JsonResponse
    {
        $this->user->updateUsersByColumn($req->id, $req->only('is_blocked'));

        return Usr::json('ok');
    }

    // ----------------------------------------------------------------------
    // Protected Helper Methods
    // ----------------------------------------------------------------------

    /**
     * Determine roles to exclude when editing based on current user's permissions.
     */
    protected function getExcludedRolesForEditing(): array
    {
        $except_role = config('roles.default_roles.tenant.student');
        $except_roles = [$except_role];

        if (! Usr::userIsHead()) {
            $except_roles[] = config('roles.default_roles.tenant.superadmin');
        }

        return $except_roles;
    }

    /**
     * Check if current user is not allowed to modify a head user.
     */
    protected function cannotModifyHeadUser(int $user_id): bool
    {
        return Usr::userIsHead($user_id) && ! Usr::userIsHead();
    }

    /**
     * Check if current user is restricted from viewing a profile.
     */
    protected function isRestrictedView(int $user_id): bool
    {
        $user = auth()->user();

        return $user_id != $user->id && ! $user->hasAnyRole(Usr::getTenancyAwareStaffRoles());
    }

    /**
     * Generate a unique staff ID based on employment date.
     */
    protected function generateStaffId($employment_date = null): string
    {
        $date = $employment_date ?? now();

        return Usr::getSystemShortCode().'/STAFF/'.date('Y/m', strtotime($date)).'/'.mt_rand(1000, 9999);
    }

    /**
     * Prepare common user data for create/update.
     */
    protected function prepareUserData(UserRequest $req, string $last_role_name, ?string $username = null, ?string $staff_id = null): array
    {
        $data = $req->except([
            ...Usr::getStaffRecordFields(),
            ...Usr::getParentRelativeRecordFields(),
            '_token',
            '_method',
            'roles',
        ]);

        $name = ucwords(strtolower($req->name));

        $data['name'] = $name;
        $data['message_media_heading_color'] = Usr::getUserMediaHeadingColor($name);
        $data['date_of_birth'] = $req->date_of_birth;

        if ($username !== null) {
            $data['username'] = $username;
        }

        // For store operation only
        if ($staff_id !== null) {
            $data['code'] = strtoupper(Str::random(10));
            $password = $req->password ?: $last_role_name;
            $data['password'] = Hash::make($password);
            $this->handlePhotoUpload($req, $data, $data['code'], $last_role_name);
        }

        return $data;
    }

    /**
     * Handle photo upload or avatar generation.
     */
    protected function handlePhotoUpload(UserRequest $req, array &$data, string $user_code, string $last_role_name): void
    {
        if ($req->hasFile('photo')) {
            $photo = $req->file('photo');
            $file_meta = Usr::getFileMetaData($photo);
            $file_meta['name'] = 'photo.'.$file_meta['ext'];
            $file_meta['path'] = $photo->storeAs(
                Usr::getUploadPath($last_role_name).$user_code,
                $file_meta['name']
            );
            $data['photo_path'] = 'storage/'.$file_meta['path'];
        } elseif ($req->route()->getActionMethod() === 'store') {
            // Only generate avatar if the request is for creating a new user.
            $data['photo_path'] = 'storage/'.Usr::createAvatar($data['name'], $user_code, $last_role_name);
        }
    }

    /**
     * Sync user permissions if provided in request.
     */
    protected function syncPermissionsIfProvided(User $user, UserRequest $req): void
    {
        if ($req->filled('permissions')) {
            $user->syncPermissions($req->permissions);
        }
    }

    /**
     * Create a staff record for a new user.
     */
    protected function createStaffRecord(int $user_id, UserRequest $req, string $staff_id): void
    {
        $staff_data = $req->only(Usr::getStaffRecordFields());
        $staff_data['user_id'] = $user_id;
        $staff_data['code'] = $staff_id;

        $this->user->createStaffRecordsQuery($staff_data);
    }

    /**
     * Update an existing staff record.
     */
    protected function updateStaffRecord(int $user_id, UserRequest $req): void
    {
        $staff_data = $req->only(Usr::getStaffRecordFields());
        $this->user->updateStaffRecord(['user_id' => $user_id], $staff_data);
    }
}
