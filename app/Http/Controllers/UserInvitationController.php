<?php

namespace App\Http\Controllers;

use App\Auth\Attributes\SkipPermission;
use App\Helpers\Qs;
use App\Http\Requests\UserInviteRequest;
use App\Http\Requests\UserRequest;
use App\Models\UserRegistrationInvite;
use App\Repositories\LocationRepo;
use App\Repositories\MyClassRepo;
use App\Repositories\PermissionRepo;
use App\Repositories\StudentRepo;
use App\Repositories\UserRepo;
use App\Services\UserService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserInvitationController extends Controller
{
    public function __construct(
        protected UserRepo $user,
        protected StudentRepo $student,
        protected LocationRepo $loc,
        protected MyClassRepo $my_class,
        protected PermissionRepo $permission,
        protected UserService $userService
    ) {
        $this->permissionMap = config('permissions.names.tenant.users');
        parent::__construct();
    }

    public function view_registrations(): View
    {
        $roles = $this->permission->getAllRoles(app(UserController::class)->getExcludedRoles());

        $data['roles'] = $roles;
        $data['blood_groups'] = $this->user->getBloodGroups();
        $data['nationals'] = $this->loc->getAllNationalities();
        $data['invitations'] = $this->user->getInvitations();
        $data['user_invite_status_approved'] = UserRegistrationInvite::STATUS_APPROVED;

        return view('pages.users.invitations.index', $data);
    }

    /**
     * Create a new invitation token with preselected roles/fields.
     */
    public function invite_user(Request $request): JsonResponse
    {
        $user_invite_request = new UserInviteRequest;
        $rules = $user_invite_request->rules();
        $rules = [...$rules, 'expires_at' => 'nullable|date|after:now'];
        $attributes = $user_invite_request->attributes();

        $request->validate($rules, ['attributes' => $attributes]);

        $invitation = $this->user->createInvitation([
            'token' => Str::random(64),
            'roles' => $request->roles,
            'prefilled_data' => $request->except([
                '_token',
                '_method',
                'expires_at',
            ]),
            'expires_at' => $request->expires_at ?? now()->addDays(3),
            'created_by' => Auth::id(),
        ]);

        $link = url("/register/{$invitation->token}");

        return Qs::json(arr: ['ok' => true, 'link' => $link]);
    }

    /**
     * Show the public registration form for a token.
     */
    #[SkipPermission]
    public function show_registration_form(string $token): View
    {
        $invitation = $this->verifyInvitationToken($token);

        $data['nationals'] = $this->loc->getAllNationalities();
        $data['blood_groups'] = $this->user->getBloodGroups();
        $user_invite_roles = \is_array($invitation->roles) ? $invitation->roles : [$invitation->roles];
        $data['roles'] = $this->permission->getAllRoles(app(UserController::class)->getExcludedRoles())
            ->filter(fn ($role) => \in_array($role->name, $user_invite_roles))
            ->values();

        $data['permissions'] = $this->permission->getAllPermissions();
        $data['prefilled'] = array_filter($invitation->prefilled_data) ?? [];
        $data['user_is_parent'] = \in_array(config('roles.default_roles.tenant.parent'), $user_invite_roles);
        $data['invitation'] = $invitation;

        return view('auth.invitation_register', $data);
    }

    /**
     * Submit the registration request.
     */
    #[SkipPermission]
    public function submit_registration(Request $request, string $token): RedirectResponse
    {
        $invitation = $this->verifyInvitationToken($token);

        $request->merge(['roles' => $invitation->roles]);
        $validator = Validator::make($request->all(), (new UserRequest)->rules());

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        $submitted_data = [...$data, ...array_filter($invitation->prefilled_data)];

        $invitation->update([
            'status' => UserRegistrationInvite::STATUS_SUBMITTED,
            'submitted_data' => $submitted_data,
            'submitted_at' => now(),
        ]);

        return redirect()->route('login')->with('invitation_registration_submitted', 'Your registration request has been submitted for review.');
    }

    /**
     * Approve a registration request and create the actual user.
     */
    public function approve(int $id): RedirectResponse
    {
        $invitation = $this->user->findInvitationOrFail($id);

        if (! $invitation->canBeApproved()) {
            return back()->with('flash_error', 'This request cannot be approved.');
        }

        $data = $invitation->submitted_data;
        $roles = $invitation->roles;

        try {
            $this->userService->storeUser($data, \is_array($roles) ? $roles : [$roles]);
        } catch (\InvalidArgumentException $e) {
            return back()->with('flash_error', $e->getMessage());
        }

        $invitation->update([
            'status' => UserRegistrationInvite::STATUS_APPROVED,
            'approved_at' => now(),
        ]);

        return back()->with('flash_success', __('msg.invitation_approved'));
    }

    /**
     * Reject a registration request.
     */
    public function reject(int $id): RedirectResponse
    {
        $invitation = $this->user->findInvitationOrFail($id);

        if (! $invitation->canBeRejected()) {
            return back()->with('flash_error', 'This request cannot be rejected.');
        }

        $invitation->update(['status' => UserRegistrationInvite::STATUS_REJECTED]);

        return back()->with('flash_success', __('msg.invitation_rejected'));
    }

    /**
     * Cancel a registration request.
     */
    public function cancel(int $id): RedirectResponse
    {
        $invitation = $this->user->findInvitationOrFail($id);

        if (! $invitation->canBeCancelled()) {
            return back()->with('flash_error', 'This request cannot be cancelled.');
        }

        $invitation->update(['status' => UserRegistrationInvite::STATUS_CANCELLED]);

        return back()->with('flash_success', __('msg.invitation_cancelled'));
    }

    /**
     * Verify the invitation token and return the invitation instance.
     */
    private function verifyInvitationToken(string $token): UserRegistrationInvite
    {
        /** @var UserRegistrationInvite $invitation */
        $invitation = $this->user->whereInvitationBuilder(['token' => $token])->firstOrFail();

        if ($invitation->isExpired()) {
            abort(404, 'This invitation has expired.');
        }

        if ($invitation->isCancelled()) {
            abort(404, 'This invitation has been cancelled.');
        }

        if (! $invitation->isPending()) {
            abort(404, 'This invitation is no longer valid. It has already been submitted, approved, rejected, or cancelled.');
        }

        return $invitation;
    }
}
