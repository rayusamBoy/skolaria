<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Http\Requests\DivisionRequest;
use App\Repositories\ExamRepo;
use App\Repositories\MyClassRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
    public function index(Request $req): View
    {
        $include_historic = $req->boolean('include_historic');

        $data['divisions'] = $this->exam->getAllDivisions();
        $data['class_types'] = $this->my_class->getAllClassTypes();
        $data['include_historic'] = $include_historic;

        return view('pages.divisions.index', $data);
    }

    /**
     * Store a newly created division.
     */
    public function store(DivisionRequest $req): JsonResponse
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
    public function update(DivisionRequest $req, int $id): JsonResponse
    {
        try {
            $data = $req->all();
            $this->exam->updateDivision($id, $data);

            return Qs::jsonUpdateOk();
        } catch (\DomainException $e) {
            return Qs::json($e->getMessage(), false);
        }
    }

    /**
     * Remove the specified division.
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $this->exam->deleteDivision($id);

            return back()->with('flash_success', __('msg.del_ok'));
        } catch (\DomainException $e) {
            return back()->with('flash_success', $e->getMessage());
        }
    }
}
