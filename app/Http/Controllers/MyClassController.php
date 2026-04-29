<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Http\Requests\MyClass\ClassCreate;
use App\Http\Requests\MyClass\ClassUpdate;
use App\Repositories\MyClassRepo;
use App\Repositories\UserRepo;

class MyClassController extends Controller
{
    protected $my_class, $user;

    public function __construct(MyClassRepo $my_class, UserRepo $user)
    {
        $this->permissionMap = config('permissions.names.tenant.my_classes');
        parent::__construct();
        $this->my_class = $my_class;
        $this->user = $user;
    }

    public function index()
    {
        $d['class_types'] = $this->my_class->getTypes();

        return view('pages.classes.index', $d);
    }

    public function store(ClassCreate $req)
    {
        $data = $req->all();
        $mc = $this->my_class->create($data);

        // Create Default Section
        $s = [
            'my_class_id' => $mc->id,
            'name' => 'A',
            'is_default' => true,
            'teacher_id' => NULL,
        ];

        $this->my_class->createSection($s);

        return Qs::jsonStoreOk();
    }

    public function edit($id)
    {
        $d['c'] = $c = $this->my_class->find($id);

        return $c === null ? Qs::goWithDanger('classes.index') : view('pages.classes.edit', $d);
    }

    public function update(ClassUpdate $req, $id)
    {
        $data = $req->only(['name']);
        $this->my_class->update($id, $data);

        return Qs::jsonUpdateOk();
    }

    public function destroy($id)
    {
        if ($this->my_class->hasStudents())
            return back()->with('pop_warning', 'This class has student(s) with it, you cannot delete it.');

        $this->my_class->delete($id);

        return back()->with('flash_success', __('msg.del_ok'));
    }
}
