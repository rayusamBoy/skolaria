<?php

namespace App\Http\Controllers;

use App\Helpers\Mk;
use App\Http\Requests\Subject\SubjectCreate;
use App\Http\Requests\Subject\SubjectRecordCreate;
use App\Http\Requests\Subject\SubjectRecordUpdate;
use App\Http\Requests\Subject\SubjectUpdate;
use App\Repositories\MyClassRepo;
use App\Repositories\UserRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function __construct(
        protected MyClassRepo $my_class,
        protected UserRepo $user,
    ) {
        $this->permissionMap = config('permissions.names.tenant.subjects');
        parent::__construct();
    }

    /**
     * Display a listing of subjects.
     */
    public function index(): View
    {
        $data['teachers'] = $this->user->getUsersByRoleNames([config('roles.default_roles.tenant.teacher')]);
        $data['subjects'] = $this->my_class->getAllSubjects();

        return view('pages.subjects.index', $data);
    }

    /**
     * Display subject records.
     */
    public function index_record(): View
    {
        $data['teachers'] = $this->user->getUsersByRoleNames([config('roles.default_roles.tenant.teacher')]);
        $data['subjects'] = $this->my_class->getAllSubjects([
            'my_class',
            'record',
            'record.section',
            'record.teacher',
        ]);

        return view('pages.subjects.records.index', $data);
    }

    /**
     * Store a newly created subject.
     */
    public function store(SubjectCreate $req): JsonResponse
    {
        $data = $req->only($this->my_class->getSubjectData(['my_class_id']));
        $class_ids = $req->validated('my_class_ids');

        foreach ($class_ids as $class_id) {
            $data['my_class_id'] = $class_id;
            $this->my_class->createSubject($data);
        }

        return Mk::jsonStoreOk();
    }

    /**
     * Store a newly created subject record.
     */
    public function store_record(SubjectRecordCreate $req): JsonResponse
    {
        if ($req->filled('students_ids')) {
            $data = $req->only($this->my_class->getSubjectRecordData(['section_id']));
        } else {
            $data = $req->only($this->my_class->getSubjectRecordData(['students_ids']));
            $data['students_ids'] = null;
        }

        $this->my_class->createSubjectRecord($data);

        return Mk::jsonStoreOk();
    }

    /**
     * Show the form for editing the specified subject.
     */
    public function edit(int $id): View|RedirectResponse
    {
        $subject = $this->my_class
            ->whereSubject(['id' => $id])
            ->first();

        if (! $subject) {
            return Mk::goWithDanger('subjects.index');
        }

        $subjects = $this->my_class->querySubject()->where([
            'name' => $subject->name,
            'is_core' => $subject->is_core,
            'slug' => $subject->slug,
            'credits' => $subject->credits,
        ])->where('id', '<>', $subject->id)->with('my_class')
            ->get()->pluck('my_class', 'id');

        return view('pages.subjects.edit', [
            'sub' => $subject,
            'my_classes' => $subjects,
        ]);
    }

    /**
     * Show the form for editing the specified subject record.
     */
    public function edit_record(int $id): View|RedirectResponse
    {
        $subject_record = $this->my_class->getSubjectRecord(['id' => $id])->first();

        if (! $subject_record) {
            return Mk::goWithDanger('subjects.index');
        }

        $data['sub_rec'] = $subject_record;
        $data['teachers'] = $this->user->getUsersByRoleNames([config('roles.default_roles.tenant.teacher')]);
        $data['my_class_sections'] = $this->my_class->getSectionsByConditions([
            'my_class_id' => $subject_record->subject->my_class_id,
        ]);

        return view('pages.subjects.records.edit', $data);
    }

    /**
     * Update the specified subject.
     */
    public function update(SubjectUpdate $req, int $id): JsonResponse
    {
        $data = $req->only($this->my_class->getSubjectData(['my_class_id']));
        $ids[] = $id;

        if($req->boolean('update_for_other_classes') === true){
            $ids = [...$ids, ...$req->validated('subject_ids')];
        }
        
        $this->my_class->updateSubject($ids, $data);

        return Mk::jsonUpdateOk();
    }

    /**
     * Update the specified subject record.
     */
    public function update_record(SubjectRecordUpdate $req, int $id): JsonResponse
    {
        if ($req->filled('students_ids')) {
            $data = $req->only($this->my_class->getSubjectRecordData(['section_id']));
            $data['section_id'] = null;
        } else {
            $data = $req->only($this->my_class->getSubjectRecordData(['students_ids']));
            $data['students_ids'] = null;
        }

        $this->my_class->updateSubjectRecords(['id' => $id], $data);

        return Mk::jsonUpdateOk();
    }

    /**
     * Remove the specified subject if it has no records.
     */
    public function destroy(int $subject): RedirectResponse
    {
        $has_records = $this->my_class->whereSubject(['id' => $subject])->with('record')->exists();
        if ($has_records) {
            return back()->with('flash_warning', __('msg.subject_in_use'));
        }

        $this->my_class->deleteSubject($subject);

        return back()->with('flash_success', __('msg.del_ok'));
    }

    /**
     * Remove the specified subject record.
     */
    public function delete_record(int $record): RedirectResponse
    {
        $this->my_class->deleteSubjectRecords(['id' => $record]);

        return back()->with('flash_success', __('msg.del_ok'));
    }
}
