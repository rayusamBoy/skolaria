<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Http\Requests\Division\DivisionCreate;
use App\Http\Requests\Division\DivisionUpdate;
use App\Repositories\ExamRepo;
use App\Repositories\MyClassRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DivisionController extends Controller
{
    public function __construct(protected ExamRepo $exam, protected MyClassRepo $my_class)
    {
        $this->permissionMap = config('permissions.names.tenant.divisions');
        parent::__construct();
    }

    /**
     * Display a listing of divisions.
     */
    public function index(): View
    {
        $data['divisions'] = $this->exam->getAllDivisions();
        $data['class_types'] = $this->my_class->getAllClassTypes();

        return view('pages.divisions.index', $data);
    }

    /**
     * Store a newly created division.
     */
    public function store(DivisionCreate $req): JsonResponse
    {
        $data = $req->all();
        $this->exam->createDivision($data);

        return Qs::jsonStoreOk();
    }

    /**
     * Show the form for editing the specified division.
     */
    public function edit(int $id): View
    {
        $data['dv'] = $this->exam->getDivisionsByConditions(['id' => $id], ['class_type'])->first();

        return view('pages.divisions.edit', $data);
    }

    /**
     * Update the specified division.
     */
    public function update(DivisionUpdate $req, int $id): JsonResponse
    {
        $data = $req->all();
        $this->exam->updateDivision($id, $data);

        return Qs::jsonUpdateOk();
    }

    /**
     * Remove the specified division.
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->exam->deleteDivision($id);

        return back()->with('flash_success', __('msg.del_ok'));
    }
}
