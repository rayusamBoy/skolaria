<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\UserRegistrationInvite;
use App\Repositories\LocationRepo;
use App\Repositories\MyClassRepo;
use App\Repositories\PermissionRepo;
use App\Repositories\StudentRepo;
use App\Repositories\UserRepo;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserInvitationController extends Controller
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

    public function index()
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
    public function store(Request $request)
    {
        $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $invitation = $this->user->createInvitation([
            'token' => Str::random(64),
            'roles' => $request->roles,
            'prefilled_data' => $request->except([
                '_token',
                '_method',
                'expires_at',
            ]),
            'expires_at' => $request->expires_at ?? now()->addDays(7),
            'created_by' => Auth::id(),
        ]);

        $link = url("/register/{$invitation->token}");

        return response()->json([
            'ok' => true,
            'link' => $link,
        ]);
    }

    /**
     * Show the public registration form for a token.
     */
    public function show_registration_form($token)
    {
        /** @var UserRegistrationInvite $invitation */
        $data['invitation'] = $invitation = $this->user->whereInvitationBuilder(['token' => $token, 'status' => 'pending'])->firstOrFail();

        if ($invitation->isExpired()) {
            abort(404, 'This invitation has expired.');
        }

        $data['nationals'] = $this->loc->getAllNationalities();
        $data['blood_groups'] = $this->user->getBloodGroups();
        $data['roles'] = $this->permission->getAllRoles(app(UserController::class)->getExcludedRoles())->filter(function ($role) use ($invitation) {
            return \in_array($role->name, $invitation->roles);
        })->values();

        $data['permissions'] = $this->permission->getAllPermissions();
        $data['prefilled'] = $invitation->prefilled_data ?? [];

        return view('auth.invitation_register', $data);
    }

    /**
     * Submit the registration request.
     */
    public function submit_registration(Request $request, $token)
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
    public function approve($id)
    {
        $invitation = $this->user->findInvitationOrFail($id);

        if ($invitation->status !== 'pending' || ! $invitation->submitted_data) {
            return back()->with('flash_error', 'This request cannot be approved.');
        }

        $data = $invitation->submitted_data;
        $roles = $invitation->roles;

        try {
            $user = $this->userService->storeUser($data, $roles);
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
    public function reject($id)
    {
        $invitation = $this->user->findInvitationOrFail($id);
        $invitation->update(['status' => 'rejected']);

        return back()->with('flash_success', __('msg.invitation_rejected'));
    }
}
