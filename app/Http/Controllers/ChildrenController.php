<?php

namespace App\Http\Controllers;

use App\Repositories\StudentRepo;
use Illuminate\View\View;

class ChildrenController extends Controller
{
    public function __construct(protected StudentRepo $student)
    {
        $this->permissionMap = config('permissions.names.tenant.childrens');
        parent::__construct();
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
