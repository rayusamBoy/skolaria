<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Http\Requests\Section\SectionCreate;
use App\Http\Requests\Section\SectionUpdate;
use App\Models\Section;
use App\Repositories\MyClassRepo;
use App\Repositories\UserRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SectionController extends Controller
{
    protected MyClassRepo $my_class;

    protected UserRepo $user;

    public function __construct(MyClassRepo $my_class, UserRepo $user)
    {
        $this->permissionMap = config('permissions.names.tenant.sections');
        parent::__construct();
        $this->my_class = $my_class;
        $this->user = $user;
    }

    /**
     * Display a listing of sections.
     */
    public function index(): View
    {
        $data['sections'] = $this->my_class->getAllSections();
        $data['teachers'] = $this->user->getUsersByRoleNames([config('roles.default_roles.tenant.teacher')]);

        return view('pages.sections.index', $data);
    }

    /**
     * Store a newly created section.
     */
    public function store(SectionCreate $req): JsonResponse
    {
        $this->my_class->createSection($req->validated());

        return Qs::jsonStoreOk();
    }

    /**
     * Show form for editing the specified section.
     */
    public function edit(Section $section): View
    {
        $data['s'] = $section;
        $data['teachers'] = $this->user->getUsersByRoleNames([config('roles.default_roles.tenant.teacher')]);

        return view('pages.sections.edit', $data);
    }

    /**
     * Update the specified section.
     */
    public function update(SectionUpdate $req, Section $section): JsonResponse
    {
        $section->update($req->only(['name', 'teacher_id']));

        return Qs::jsonUpdateOk();
    }

    /**
     * Remove the specified section.
     */
    public function destroy(Section $section): RedirectResponse
    {
        if ($section->is_default) {
            return back()->with('pop_warning', 'This is a default section, You cannot delete It.');
        }

        if ($section->hasStudentRecord()) {
            return back()->with('pop_warning', 'This section has student records, You cannot delete It.');
        }

        $section->delete();

        return back()->with('flash_success', __('msg.del_ok'));
    }
}
