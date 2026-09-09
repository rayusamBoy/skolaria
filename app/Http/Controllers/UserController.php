<?php

namespace App\Http\Controllers;

use App\Auth\Attributes\SkipPermission;
use App\Helpers\Usr;
use App\Http\Requests\UserBlockedState;
use App\Http\Requests\UserRequest;
use App\Http\Requests\UserStaffDataEditState;
use App\Repositories\LocationRepo;
use App\Repositories\MyClassRepo;
use App\Repositories\PermissionRepo;
use App\Repositories\StudentRepo;
use App\Repositories\UserRepo;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserController extends Controller
{
    protected UserRepo $user;

    protected StudentRepo $student;

    protected LocationRepo $loc;

    protected MyClassRepo $my_class;

    protected PermissionRepo $permission;

    protected UserService $userService;

    public function __construct(
        UserRepo $user,
        StudentRepo $student,
        LocationRepo $loc,
        MyClassRepo $my_class,
        PermissionRepo $permission,
        UserService $userService
    ) {
        $this->permissionMap = config('permissions.names.tenant.users');
        parent::__construct();
        $this->user = $user;
        $this->student = $student;
        $this->loc = $loc;
        $this->my_class = $my_class;
        $this->permission = $permission;
        $this->userService = $userService;
    }

    /**
     * Display list of tenant users.
     */
    public function index(): View
    {
        $data['nationals'] = $this->loc->getAllNationalities();
        $data['blood_groups'] = $this->user->getBloodGroups();
        $data['roles'] = $this->permission->getAllRoles($this->getExcludedRoles());
        $data['permissions'] = $this->permission->getAllPermissions();

        return view('pages.users.index', $data);
    }

    /**
     * Show form to edit a user.
     */
    public function edit($user): View
    {
        $roles = $this->permission->getAllRoles($this->getExcludedRoles());

        $data['roles'] = $roles;
        $data['permissions'] = $this->permission->getAllPermissions();
        $data['user'] = $this->user->findUser($user);
        $data['staff_rec'] = $this->user->getStaffRecordsByConditions(['user_id' => $user])->first();
        $data['users'] = $this->user->getUsersByRoleNames($roles->pluck('name')->toArray());
        $data['blood_groups'] = $this->user->getBloodGroups();
        $data['nationals'] = $this->loc->getAllNationalities();

        return view('pages.users.edit', $data);
    }

    /**
     * Reset a user's password to 'user'.
     */
    public function reset_password($user): RedirectResponse
    {
        if ($this->cannotModifyHeadUser($user)) {
            return back()->with('flash_danger', __('msg.denied'));
        }

        $this->user->updateUsersByColumn($user, [
            'password' => Hash::make('user'),
            'password_updated_at' => null,
        ]);

        return back()->with('flash_success', __('msg.pu_reset'));
    }

    /**
     * Store a new user.
     */
    public function store(UserRequest $req): JsonResponse
    {
        $role_names = $req->roles;
        $last_role_name = strtolower(last($role_names));

        $data = $req->validated();
        $permissions = $req->filled('permissions') ? $req->permissions : null;

        try {
            $user = $this->userService->storeUser($data, $role_names, $permissions);
        } catch (\InvalidArgumentException $e) {
            return Usr::json($e->getMessage(), false);
        }

        return Usr::json(__('msg.store_ok')." Default password: $last_role_name");
    }

    /**
     * Update an existing user.
     */
    public function update(UserRequest $req, $user_id): JsonResponse
    {
        if ($this->cannotModifyHeadUser($user_id)) {
            return Usr::json(__('msg.denied'), false);
        }

        $role_names = $req->roles;
        $permissions = $req->filled('permissions') ? $req->permissions : null;

        try {
            $this->userService->updateUser($user_id, $req->validated(), $role_names, $permissions);
        } catch (\Throwable $e) {
            return Usr::json($e->getMessage(), false);
        }

        return Usr::jsonUpdateOk();
    }

    /**
     * Show own profile.
     */
    #[SkipPermission]
    public function show_profile($user_id)
    {
        return $this->show($user_id);
    }

    /**
     * Show user details.
     */
    public function show($user_id): View|RedirectResponse
    {
        if (! $user_id) {
            return back();
        }

        if ($this->isRestrictedView($user_id)) {
            return back()->with('pop_error', __('msg.denied'));
        }

        $user = $this->user->findUser($user_id);
        $user_is_parent = $user->hasRole(config('roles.default_roles.tenant.parent'));
        $data['user_is_parent'] = $user_is_parent;
        $data['user'] = $user;

        if ($this->hasStaffRole($user->roles->pluck('name')->toArray())) {
            $data['staff_rec'] = $this->user->getStaffRecordsByConditions(['user_id' => $user->id])->first();
        }

        if ($user_is_parent) {
            $data['my_childrens'] = $this->student->getStudentRecords(['my_parent_id' => $user->id], ['user', 'my_class']);
        }

        if ($user->hasRole(config('roles.default_roles.tenant.teacher'))) {
            $data['sub_recs'] = $this->my_class->getSubjectRecord(['teacher_id' => $user->id]);
        }

        return view('pages.users.show', $data);
    }

    /**
     * Delete a user.
     */
    public function destroy($user): RedirectResponse
    {
        if (Usr::userIsHead($user)) {
            return back()->with('pop_error', __('msg.denied'));
        }

        $user = $this->user->findUser($user);

        if ($user->hasRole(config('roles.default_roles.tenant.teacher')) && $this->userTeachesSubject($user->id)) {
            return back()->with('pop_error', __('msg.del_teacher'));
        }

        $user->delete();

        return back()->with('flash_success', __('msg.del_ok'));
    }

    /**
     * Toggle staff data editing permission.
     */
    public function toggle_staff_data_editing(UserStaffDataEditState $req): JsonResponse
    {
        $this->user->updateStaffRecord(
            ['user_id' => $req->id],
            $req->input('allow_staff_data_edit')
        );

        return Usr::json('ok');
    }

    /**
     * Toggle user is_blocked status.
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
     * Get roles excluded from selection based on current user's permissions.
     */
    #[SkipPermission]
    public function getExcludedRoles(): array
    {
        $except_role = config('roles.default_roles.tenant.student');
        $except_roles = [$except_role];

        if (! Usr::userIsHead()) {
            $except_roles[] = config('roles.default_roles.tenant.superadmin');
        }

        return $except_roles;
    }

    /**
     * Check if current user is allowed to modify a head user.
     */
    protected function cannotModifyHeadUser($user_id): bool
    {
        return Usr::userIsHead($user_id) && ! Usr::userIsHead();
    }

    /**
     * Check if current user is restricted from viewing a profile.
     */
    protected function isRestrictedView($user_id): bool
    {
        $user = auth()->user();

        return $user_id != $user->id && ! $user->hasAnyRole(Usr::getTenancyAwareStaffRoles());
    }

    /**
     * Determine if any of the given roles are staff roles.
     */
    protected function hasStaffRole(array $roles): bool
    {
        return \count(array_intersect($roles, Usr::getStaffRoleNames())) > 0;
    }

    /**
     * Prepare common user data for create/update.
     */
    protected function prepareUserData(
        UserRequest $req,
        string $last_role_name,
        bool $user_is_staff,
        ?string $existing_code = null
    ): array {
        $exclude = [
            ...Usr::getStaffRecordFields(),
            ...Usr::getParentRelativeRecordFields(),
            '_token',
            '_method',
        ];

        $data = $req->except($exclude);

        $data['name'] = $name = ucwords(strtolower($req->name));
        $data['message_media_heading_color'] = Usr::getUserMediaHeadingColor($name);
        $data['date_of_birth'] = $req->date_of_birth;

        if ($existing_code === null) {
            // New user
            $data['code'] = strtoupper(Str::random(10));
            $data['username'] = $user_is_staff
                ? $this->generateStaffId($req->employment_date)
                : $req->username;
            $this->handlePhotoUpload($req, $data, $data['code'], $last_role_name);
        } else {
            // Existing user (update)
            $data['code'] = $existing_code;
        }

        return $data;
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
     * Handle photo upload or avatar generation.
     */
    protected function handlePhotoUpload(
        UserRequest $req,
        array &$data,
        string $user_code,
        string $last_role_name
    ): void {
        if ($req->hasFile('photo')) {
            $photo = $req->file('photo');
            $file_meta = Usr::getFileMetaData($photo);
            $file_meta['name'] = 'photo.'.$file_meta['ext'];
            $file_meta['path'] = Usr::getUploadPath($last_role_name).$user_code.'/'.$file_meta['name'];
            $photo->storeAs($file_meta['path']);
            $data['photo_path'] = $file_meta['path'];
        } elseif ($req->route()->getActionMethod() === 'store') {
            $data['photo_path'] = Usr::createAvatar($data['name'], $user_code, $last_role_name);
        }
    }

    /**
     * Create a staff record for a user.
     */
    protected function createStaffRecord(int $user_id, UserRequest $req): void
    {
        $staff_data = $req->only(Usr::getStaffRecordFields());
        $staff_data['user_id'] = $user_id;
        $staff_data['code'] = $this->generateStaffId($req->employment_date);

        $this->user->createStaffRecordsQuery($staff_data);
    }

    /**
     * Create a parent relative record.
     */
    protected function createParentRecord(int $user_id, UserRequest $req): void
    {
        $parent_data = $req->only(Usr::getParentRelativeRecordFields(['relative_name']));
        $parent_data['user_id'] = $user_id;
        $parent_data['name'] = ucwords(strtolower($req->relative_name));
        $parent_data['relation'] = $req->relation;

        $this->user->createParentRelativeRecord($parent_data);
    }

    /**
     * Handle staff record creation, update, or deletion during user update.
     */
    protected function handleStaffRecordTransition(
        int $user_id,
        bool $was_staff,
        bool $is_staff,
        UserRequest $req
    ): void {
        if ($was_staff) {
            if ($is_staff) {
                $staff_data = $req->only(Usr::getStaffRecordFields());
                $this->user->updateStaffRecord(['user_id' => $user_id], $staff_data);
            } else {
                $this->user->deleteStaffRecords(['user_id' => $user_id]);
            }
        } elseif ($is_staff) {
            $staff_data = $req->only(Usr::getStaffRecordFields());
            $staff_data['user_id'] = $user_id;
            $staff_data['code'] = $this->generateStaffId($req->employment_date);
            $this->user->createStaffRecordsQuery($staff_data);
        }
    }

    /**
     * Update or create parent relative record if user is a parent.
     */
    protected function handleParentRecordUpdate(int $user_id, bool $is_parent, UserRequest $req): void
    {
        if (! $is_parent) {
            return;
        }

        $parent_data = $req->only(Usr::getParentRelativeRecordFields(['user_id', 'relative_name']));
        $parent_data['name'] = ucwords(strtolower($req->relative_name));
        $parent_data['relation'] = $req->relation;

        $this->user->updateOrCreateParentRelative(['user_id' => $user_id], $parent_data);
    }

    /**
     * Check if a teacher teaches any subject.
     */
    protected function userTeachesSubject($user_id): bool
    {
        return $this->my_class->findSubjectRecordsByTeacher($user_id)->isNotEmpty();
    }
}
