<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Repositories\StudentRepo;

class ChildrenController extends Controller
{
    protected $student;
    public function __construct(StudentRepo $student)
    {
        $this->permissionMap = config('permissions.names.tenant.childrens');
        parent::__construct();
        $this->student = $student;
    }

    public function children()
    {
        $data['students'] = $this->student->whereRecord(['my_parent_id' => auth()->id()])->with(['my_class', 'section'])->get();

        return view('pages.children', $data);
    }
}