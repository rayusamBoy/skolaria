<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Http\Requests\MyClass\ClassCreate;
use App\Http\Requests\MyClass\ClassUpdate;
use App\Repositories\MyClassRepo;
use App\Repositories\UserRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MyClassController extends Controller
{
    protected MyClassRepo $my_class;

    protected UserRepo $user;

    public function __construct(MyClassRepo $my_class, UserRepo $user)
    {
        $this->permissionMap = config('permissions.names.tenant.my_classes');
        parent::__construct();
        $this->my_class = $my_class;
        $this->user = $user;
    }

    /**
     * Display a listing of classes.
     */
    public function index(): View
    {
        $data['class_types'] = $this->my_class->getAllClassTypes();

        return view('pages.classes.index', $data);
    }

    /**
     * Store a newly created class with a default Section 'A'.
     */
    public function store(ClassCreate $req): JsonResponse
    {
        $data = $req->all();
        $class = $this->my_class->createClass($data);

        $this->my_class->createSection([
            'my_class_id' => $class->id,
            'name' => 'A',
            'is_default' => true,
            'teacher_id' => null,
        ]);

        return Qs::jsonStoreOk();
    }

    /**
     * Show the form for editing the specified class.
     */
    public function edit(int $id): View|RedirectResponse
    {
        $class = $this->my_class->findClass($id);

        return $class !== null
            ? view('pages.classes.edit', ['c' => $class])
            : Qs::goWithDanger('classes.index');
    }

    /**
     * Update the specified class.
     */
    public function update(ClassUpdate $req, int $id): JsonResponse
    {
        $data = $req->only(['name']);
        $this->my_class->updateClass($id, $data);

        return Qs::jsonUpdateOk();
    }

    /**
     * Remove the specified class if it has no students.
     */
    public function destroy(int $id): RedirectResponse
    {
        if ($this->my_class->hasAnyClassStudents($id)) {
            return back()->with('pop_warning', 'This class has student(s) with it, you cannot delete it.');
        }

        $this->my_class->deleteClass($id);

        return back()->with('flash_success', __('msg.del_ok'));
    }
}
