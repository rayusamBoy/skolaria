<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Http\Requests\Division\DivisionCreate;
use App\Http\Requests\Division\DivisionUpdate;
use App\Repositories\ExamRepo;
use App\Repositories\MyClassRepo;

class DivisionController extends Controller
{
    protected $exam, $my_class;

    public function __construct(ExamRepo $exam, MyClassRepo $my_class)
    {
        $this->permissionMap = config('permissions.names.tenant.divisions');
        parent::__construct();
        $this->exam = $exam;
        $this->my_class = $my_class;
    }

    public function index()
    {
        $d['divisions'] = $this->exam->allDivisions();
        $d['class_types'] = $this->my_class->getTypes();

        return view('pages.divisions.index', $d);
    }

    public function store(DivisionCreate $req)
    {
        $data = $req->all();
        $this->exam->createDivision($data);

        return Qs::jsonStoreOk();
    }

    public function edit($id)
    {
        $d['dv'] = $this->exam->getDivision(['id' => $id], ['class_type'])->first();

        return view('pages.divisions.edit', $d);
    }

    public function update(DivisionUpdate $req, $id)
    {
        $data = $req->all();
        $this->exam->updateDivision($id, $data);

        return Qs::jsonUpdateOk();
    }

    public function destroy($id)
    {
        $this->exam->deleteDivision($id);

        return back()->with('flash_success', __('msg.del_ok'));
    }
}
