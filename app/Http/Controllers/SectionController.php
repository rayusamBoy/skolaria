<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Http\Requests\Section\SectionCreate;
use App\Http\Requests\Section\SectionUpdate;
use App\Repositories\MyClassRepo;
use App\Repositories\UserRepo;

class SectionController extends Controller
{
    protected $my_class, $user;

    public function __construct(MyClassRepo $my_class, UserRepo $user)
    {
        $this->permissionMap = config('permissions.names.tenant.sections');
        parent::__construct();
        $this->my_class = $my_class;
        $this->user = $user;
    }

    public function index()
    {
        $d['sections'] = $this->my_class->getAllSections();
        $d['teachers'] = $this->user->getUsersByRoleNames([config('roles.default_roles.tenant.teacher')]);

        return view('pages.sections.index', $d);
    }

    public function store(SectionCreate $req)
    {
        $data = $req->all();
        $this->my_class->createSection($data);

        return Qs::jsonStoreOk();
    }

    public function edit($id)
    {
        $d['s'] = $s = $this->my_class->findSection($id);
        $d['teachers'] = $this->user->getUsersByRoleNames([config('roles.default_roles.tenant.teacher')]);

        return $s === null ? Qs::goWithDanger('sections.index') : view('pages.sections.edit', $d);
    }

    public function update(SectionUpdate $req, $id)
    {
        $data = $req->only(['name', 'teacher_id']);
        $this->my_class->updateSection($id, $data);

        return Qs::jsonUpdateOk();
    }

    public function destroy($id)
    {
        if ($this->my_class->isActiveSection($id))
            return back()->with('pop_warning', 'Every class must have a default section, You cannot delete It.');

        if ($this->my_class->sectionHasStudent($id))
            return back()->with('pop_warning', 'This section has student(s) with it, you cannot delete it.');

        $this->my_class->deleteSection($id);

        return back()->with('flash_success', __('msg.del_ok'));
    }
}
