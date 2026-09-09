<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Http\Requests\Grade\GradeCreate;
use App\Http\Requests\Grade\GradeUpdate;
use App\Repositories\ExamRepo;
use App\Repositories\MyClassRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GradeController extends Controller
{
    protected ExamRepo $exam;

    protected MyClassRepo $my_class;

    public function __construct(ExamRepo $exam, MyClassRepo $my_class)
    {
        $this->permissionMap = config('permissions.names.tenant.grades');
        parent::__construct();
        $this->exam = $exam;
        $this->my_class = $my_class;
    }

    /**
     * Display a listing of grades.
     */
    public function index(): View
    {
        $data['grades'] = $this->exam->getAllGrades();
        $data['class_types'] = $this->my_class->getAllClassTypes();

        return view('pages.grades.index', $data);
    }

    /**
     * Store a newly created grade.
     */
    public function store(GradeCreate $req): JsonResponse
    {
        $data = $req->all();
        $this->exam->createGrade($data);

        return Qs::jsonStoreOk();
    }

    /**
     * Show the form for editing the specified grade.
     */
    public function edit($id): View
    {
        $data['gr'] = $this->exam->getGradesByConditions(['id' => $id], ['class_type'])->first();

        return view('pages.grades.edit', $data);
    }

    /**
     * Update the specified grade.
     */
    public function update(GradeUpdate $req, $id): JsonResponse
    {
        $data = $req->except(['_token', '_method']);
        $this->exam->updateGrade($id, $data);

        return Qs::jsonUpdateOk();
    }

    /**
     * Remove the specified grade.
     */
    public function destroy($id): RedirectResponse
    {
        $this->exam->deleteGrade($id);

        return back()->with('flash_success', __('msg.del_ok'));
    }
}
