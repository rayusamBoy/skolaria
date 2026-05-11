<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Repositories\StudentRepo;
use Illuminate\View\View;

class ChildrenController extends Controller
{
    protected StudentRepo $student;

    public function __construct(StudentRepo $student)
    {
        $this->permissionMap = config('permissions.names.tenant.childrens');
        parent::__construct();
        $this->student = $student;
    }

    /**
     * Display a listing of the authenticated parent's children.
     */
    public function children(): View
    {
        $data['students'] = $this->student
            ->getActiveStudentsQueryWithRelations(['my_parent_id' => auth()->id()])
            ->with(['my_class', 'section'])
            ->get();

        return view('pages.children', $data);
    }
}
