<?php

namespace App\Http\Controllers;

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

        return back()->with('flash_success', __('msg.store_ok'));
    }

    public function edit($id)
    {
        $d['class_types'] = $this->my_class->getTypes();
        $d['dv'] = $this->exam->findDivision($id);

        return view('pages.divisions.edit', $d);
    }

    public function update(DivisionUpdate $req, $id)
    {
        $data = $req->all();
        $this->exam->updateDivision($id, $data);

        return back()->with('flash_success', __('msg.update_ok'));
    }

    public function destroy($id)
    {
        $this->exam->deleteDivision($id);

        return back()->with('flash_success', __('msg.del_ok'));
    }
}
