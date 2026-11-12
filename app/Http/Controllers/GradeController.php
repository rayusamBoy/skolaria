<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Http\Requests\GradeRequest;
use App\Repositories\ExamRepo;
use App\Repositories\MyClassRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PermissionKit\Concerns\AuthorizesPermissions;

class GradeController extends Controller
{
    use AuthorizesPermissions;

    public function __construct(protected ExamRepo $exam, protected MyClassRepo $my_class) {}

    /**
     * Display a listing of grades.
     */
    public function index(Request $req): View
    {
        $include_historic = $req->boolean('include_historic');

        $data['grades'] = $this->exam->getAllGrades();
        $data['class_types'] = $this->my_class->getAllClassTypes();
        $data['include_historic'] = $include_historic;

        return view('pages.grades.index', $data);
    }

    /**
     * Store a newly created grade.
     */
    public function store(GradeRequest $req): JsonResponse
    {
        $data = $req->all();
        $this->exam->createGrade($data);

        return Qs::jsonStoreOk();
    }

    /**
     * Show the form for editing the specified grade.
     */
    public function edit(int $id): View
    {
        $data['gr'] = $this->exam->getGradesByConditions(['id' => $id], ['class_type'])->first();

        return view('pages.grades.edit', $data);
    }

    /**
     * Update the specified grade.
     */
    public function update(GradeRequest $req, int $id): JsonResponse
    {
        try {
            $data = $req->except(['_token', '_method']);
            $this->exam->updateGrade($id, $data);

            return Qs::jsonUpdateOk();
        } catch (\DomainException $e) {
            return Qs::json($e->getMessage(), false);
        }
    }

    /**
     * Remove the specified grade.
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $this->exam->deleteGrade($id);

            return back()->with('flash_success', __('msg.del_ok'));
        } catch (\DomainException $e) {

            return back()->with('flash_success', $e->getMessage());

        }
    }
}
