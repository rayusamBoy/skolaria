<?php

namespace App\Http\Controllers;

use App\Helpers\Mk;
use App\Http\Controllers\Controller;
use App\Http\Requests\Subject\SubjectCreate;
use App\Http\Requests\Subject\SubjectRecordUpdate;
use App\Repositories\MyClassRepo;
use App\Repositories\UserRepo;

class SubjectController extends Controller
{
    protected $my_class, $user;

    public function __construct(MyClassRepo $my_class, UserRepo $user)
    {
        $this->my_class = $my_class;
        $this->user = $user;
    }

    public function index()
    {
        $d['my_classes'] = $this->my_class->all();
        $d['teachers'] = $this->user->getUsersByRoleNames(['Teacher', 'Admin', 'Super Admin']);
        $d['subjects'] = $this->my_class->getAllSubjects();

        return view('pages.subjects.index', $d);
    }

    public function store(SubjectCreate $req)
    {
        $d = $req->only($this->my_class->getSubjectData());

        if ($req->students_ids != null) {
            $d2 = $req->only($this->my_class->getSubjectRecordData(['section_id']));
            $d2['students_ids'] = serialize($d2['students_ids']);
        } else {
            $d2 = $req->only($this->my_class->getSubjectRecordData(['students_ids']));
            $d2['students_ids'] = null;
        }

        $subject = $this->my_class->getSubject(['name' => $req->name, 'my_class_id' => $req->my_class_id])->with('record')->first();

        if ($subject !== null) {
            if ($subject->record->where('section_id', null)->isNotEmpty())
                return Mk::json(__('msg.class_sub_exists'), false);
            $d2['subject_id'] = $subject->id;
        } else {
            $new_subject = $this->my_class->createSubject($d);
            $d2['subject_id'] = $new_subject->id;
        }

        $this->my_class->createSubjectRecord($d2);

        return Mk::jsonStoreOk();
    }

    public function edit_record($id)
    {
        $d['sub_rec'] = $sub_rec = $this->my_class->getSubjectRecord(['id' => $id])->first();
        $d['teachers'] = $this->user->getUsersByRoleNames(['Teacher', 'Admin', 'Super Admin']);

        return $sub_rec === null ? Mk::goWithDanger('subjects.index') : view('pages.subjects.edit_record', $d);
    }

    public function update_record(SubjectRecordUpdate $req, $id)
    {
        $d = $req->only($this->my_class->getSubjectData(['name', 'my_class_id']));
        $teacher_id = $req->teacher_id;
        $students_ids = $req->students_ids ? serialize($req->students_ids) : null;

        $this->my_class->updateSubject($req->subject_id, $d);
        $this->my_class->updateSubjectRecord(['id' => $id], ['teacher_id' => $teacher_id, 'students_ids' => $students_ids]);

        return Mk::jsonUpdateOk();
    }

    public function delete_record($sub_id, $id)
    {
        $this->my_class->deleteSubjectRecord(['id' => $id]); // Delete subject record

        if (!MK::subjectHasChildRows($sub_id))
            $this->my_class->deleteSubject($sub_id); // Delete the subject as well.

        return back()->with('flash_success', __('msg.del_ok'));
    }
}
