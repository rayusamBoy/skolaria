<?php

namespace App\Http\Controllers;

use App\Helpers\Mk;
use App\Http\Controllers\Controller;
use App\Http\Requests\Subject\SubjectCreate;
use App\Http\Requests\Subject\SubjectRecordCreate;
use App\Http\Requests\Subject\SubjectRecordUpdate;
use App\Http\Requests\Subject\SubjectUpdate;
use App\Repositories\MyClassRepo;
use App\Repositories\UserRepo;

class SubjectController extends Controller
{
    protected $my_class, $user;

    public function __construct(MyClassRepo $my_class, UserRepo $user)
    {
        $this->permissionMap = config('permissions.names.tenant.subjects');
        parent::__construct();
        $this->my_class = $my_class;
        $this->user = $user;
    }

    public function index()
    {
        $d['teachers'] = $this->user->getUsersByRoleNames([config('roles.default_roles.tenant.teacher')]);
        $d['subjects'] = $this->my_class->getAllSubjects();

        return view('pages.subjects.index', $d);
    }

    public function index_record()
    {
        $d['teachers'] = $this->user->getUsersByRoleNames([config('roles.default_roles.tenant.teacher')]);
        $d['subjects'] = $this->my_class->getAllSubjects();

        return view('pages.subjects.records.index', $d);
    }

    public function store(SubjectCreate $req)
    {
        $d = $req->only($this->my_class->getSubjectData());
        $this->my_class->createSubject($d);

        return Mk::jsonStoreOk();
    }

    public function store_record(SubjectRecordCreate $req)
    {
        if ($req->filled('students_ids')) {
            $d = $req->only($this->my_class->getSubjectRecordData(['section_id']));
        } else {
            $d = $req->only($this->my_class->getSubjectRecordData(['students_ids']));
            $d['students_ids'] = null;
        }

        $this->my_class->createSubjectRecord($d);

        return Mk::jsonStoreOk();
    }

    public function edit($id)
    {
        $sub = $this->my_class->findSubject($id);
        $d['sub'] = $sub;

        return $sub->count() > 0 ? view('pages.subjects.edit', $d) : Mk::goWithDanger('subjects.index');
    }

    public function edit_record($id)
    {
        $d['sub_rec'] = $sub_rec = $this->my_class->getSubjectRecord(['id' => $id])->first();
        $d['teachers'] = $this->user->getUsersByRoleNames([config('roles.default_roles.tenant.teacher')]);
        $d['my_class_sections'] = $this->my_class->getSections(['my_class_id' => $sub_rec->subject->my_class_id]);

        return $sub_rec->exists() ? view('pages.subjects.records.edit', $d) : Mk::goWithDanger('subjects.index');
    }

    public function update(SubjectUpdate $req, $id)
    {
        $d = $req->only($this->my_class->getSubjectData(['my_class_id']));

        $this->my_class->updateSubject($id, $d);

        return Mk::jsonUpdateOk();
    }

    public function update_record(SubjectRecordUpdate $req, $id)
    {
        if ($req->filled('students_ids')) {
            $d = $req->only($this->my_class->getSubjectRecordData(['section_id']));
            $d['section_id'] = null;
        } else {
            $d = $req->only($this->my_class->getSubjectRecordData(['students_ids']));
            $d['students_ids'] = null;
        }

        $this->my_class->updateSubjectRecord(['id' => $id], $d);

        return Mk::jsonUpdateOk();
    }

    public function destroy($sub_id)
    {
        $this->my_class->deleteSubject($sub_id);

        return back()->with('flash_success', __('msg.del_ok'));
    }

    public function delete_record($id)
    {
        $this->my_class->deleteSubjectRecord(['id' => $id]); // Delete subject record

        return back()->with('flash_success', __('msg.del_ok'));
    }
}
