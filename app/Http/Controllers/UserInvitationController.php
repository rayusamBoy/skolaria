<?php

namespace App\Http\Controllers;

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

    public function index(): View
    {
        $roles = $this->permission->getAllRoles(app(UserController::class)->getExcludedRoles());

        $data['roles'] = $roles;
        $data['blood_groups'] = $this->user->getBloodGroups();
        $data['nationals'] = $this->loc->getAllNationalities();
        $data['invitations'] = $this->user->getInvitations();

        return view('pages.users.invitations.index', $data);
    }

    /**
     * Create a new invitation token with preselected roles/fields.
     */
    public function store(Request $request): JsonResponse
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
    public function show_registration_form(string $token): View
    {
        /** @var UserRegistrationInvite $invitation */
        $data['invitation'] = $invitation = $this->user->whereInvitationBuilder(['token' => $token, 'status' => 'pending'])->firstOrFail();

        if ($invitation->isExpired()) {
            abort(404, 'This invitation has expired.');
        }

        $data['nationals'] = $this->loc->getAllNationalities();
        $data['blood_groups'] = $this->user->getBloodGroups();
        $user_invite_roles = \is_array($invitation->roles) ? $invitation->roles : [$invitation->roles];
        $data['roles'] = $this->permission->getAllRoles(app(UserController::class)->getExcludedRoles())->filter(fn ($role) => \in_array($role->name, $user_invite_roles))->values();

        $data['permissions'] = $this->permission->getAllPermissions();
        $data['prefilled'] = $invitation->prefilled_data ?? [];

        return view('auth.invitation_register', $data);
    }

    /**
     * Submit the registration request.
     */
    public function submit_registration(Request $request, string $token): RedirectResponse
    {
        $validator = Validator::make($request->all(), (new UserRequest)->rules());

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        /** @var UserRegistrationInvite $invitation */
        $invitation = $this->user->whereInvitationBuilder(['token' => $token, 'status' => 'pending'])->firstOrFail();

        if ($invitation->isExpired()) {
            abort(404, 'This invitation has expired.');
        }

        $data = $validator->validated();

        // Merge the submitted data with prefilled data, but allow override only where needed.
        // For security, only store what is submitted + prefilled.
        $submittedData = [...$invitation->prefilled_data ?? [], ...$data];

        $invitation->update([
            'status' => 'submitted',
            'submitted_data' => $submittedData,
            'submitted_at' => now(),
        ]);

        return redirect()->route('login')->with('flash_info', 'Your registration request has been submitted for review.');
    }

    /**
     * Admin – approve a registration request and create the actual user.
     */
    public function approve(int $id): RedirectResponse
    {
        $invitation = $this->user->findInvitationOrFail($id);

        if ($invitation->status !== 'pending' || ! $invitation->submitted_data) {
            return back()->with('flash_error', 'This request cannot be approved.');
        }

        $data = $invitation->submitted_data;
        $roles = $invitation->roles;

        try {
            $this->userService->storeUser($data, $roles);
        } catch (\InvalidArgumentException $e) {
            return back()->with('flash_error', $e->getMessage());
        }

        $invitation->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        return back()->with('flash_success', __('msg.invitation_approved'));
    }

    /**
     * Admin – reject a registration request.
     */
    public function reject(int $id): RedirectResponse
    {
        $invitation = $this->user->findInvitationOrFail($id);
        $invitation->update(['status' => 'rejected']);

        return back()->with('flash_success', __('msg.invitation_rejected'));
    }
}
