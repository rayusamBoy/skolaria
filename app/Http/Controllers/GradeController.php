<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Http\Requests\Grade\GradeCreate;
use App\Http\Requests\Grade\GradeUpdate;
use App\Repositories\ExamRepo;
use App\Repositories\MyClassRepo;

class GradeController extends Controller
{
    protected $exam, $my_class;

    public function __construct(ExamRepo $exam, MyClassRepo $my_class)
    {
        $this->permissionMap = config('permissions.names.tenant.grades');
        parent::__construct();
        $this->exam = $exam;
        $this->my_class = $my_class;
    }

    public function index()
    {
        $d['grades'] = $this->exam->allGrades();
        $d['class_types'] = $this->my_class->getTypes();

        return view('pages.grades.index', $d);
    }

    public function store(GradeCreate $req)
    {
        $data = $req->all();
        $this->exam->createGrade($data);

        return Qs::jsonStoreOk();
    }

    public function edit($id)
    {
        $d['gr'] = $this->exam->getGrade(['id' => $id], ['class_type'])->first();

        return view('pages.grades.edit', $d);
    }

    public function update(GradeUpdate $req, $id)
    {
        $data = $req->all();
        $this->exam->updateGrade($id, $data);

        return Qs::jsonUpdateOk();
    }

    public function destroy($id)
    {
        $this->exam->deleteGrade($id);

        return back()->with('flash_success', __('msg.del_ok'));
    }
}
