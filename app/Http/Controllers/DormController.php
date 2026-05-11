<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dorm\DormCreate;
use App\Http\Requests\Dorm\DormUpdate;
use App\Repositories\DormRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DormController extends Controller
{
    protected DormRepo $dorm;

    public function __construct(DormRepo $dorm)
    {
        $this->permissionMap = config('permissions.names.tenant.dorms');
        parent::__construct();
        $this->dorm = $dorm;
    }

    /**
     * Display a listing of dormitories.
     */
    public function index(): View
    {
        $data['dorms'] = $this->dorm->getAllDorms();

        return view('pages.dorms.index', $data);
    }

    /**
     * Store a newly created dormitory.
     */
    public function store(DormCreate $req): JsonResponse
    {
        $data = $req->only(['name', 'description']);
        $this->dorm->createDorm($data);

        return Qs::jsonStoreOk();
    }

    /**
     * Show the form for editing the specified dormitory.
     */
    public function edit(int $id): View|RedirectResponse
    {
        $dorm = $this->dorm->findDorm($id);

        return $dorm !== null
            ? view('pages.dorms.edit', ['dorm' => $dorm])
            : Qs::goWithDanger('dorms.index');
    }

    /**
     * Update the specified dormitory.
     */
    public function update(DormUpdate $req, int $id): JsonResponse
    {
        $data = $req->only(['name', 'description']);
        $this->dorm->updateDorm($id, $data);

        return Qs::jsonUpdateOk();
    }

    /**
     * Remove the specified dormitory.
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->dorm->findDorm($id)->delete();

        return back()->with('flash_success', __('msg.delete_ok'));
    }
}
