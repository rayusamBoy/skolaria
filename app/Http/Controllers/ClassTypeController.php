<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Http\Requests\ClassType\ClassTypeCreate;
use App\Http\Requests\ClassType\ClassTypeUpdate;
use App\Models\ClassType;
use App\Repositories\MyClassRepo;
use App\Repositories\SettingRepo;
use App\Repositories\UserRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClassTypeController extends Controller
{
    public function __construct(protected MyClassRepo $my_class, protected UserRepo $user, protected SettingRepo $setting)
    {
        $this->permissionMap = config('permissions.names.tenant.class_types');
        parent::__construct();
    }

    /**
     * Display a listing of class types.
     */
    public function index(): View
    {
        $data['class_types'] = $this->my_class->getAllClassTypes();

        return view('pages.class_types.index', $data);
    }

    /**
     * Store a newly created class type.
     */
    public function store(ClassTypeCreate $req): JsonResponse
    {
        $this->my_class->createClassType($req->validated());
        $this->setting->create('next_term_fees_'.strtolower($req->validated('code')), '0000');

        return Qs::jsonStoreOk();
    }

    /**
     * Show form for editing the specified class type.
     */
    public function edit(ClassType $class_type): View
    {
        $data['class_type'] = $class_type;

        return view('pages.class_types.edit', $data);
    }

    /**
     * Update the specified class type.
     */
    public function update(ClassTypeUpdate $req, ClassType $class_type): JsonResponse
    {
        $current_code = $class_type->code;
        $request_code = $req->validated('code');

        $class_type->update($req->validated());
        if (strtolower($current_code) != strtolower($request_code)) {
            $this->setting->updateKey('next_term_fees_'.strtolower($current_code), 'next_term_fees_'.strtolower($request_code));
        }

        return Qs::jsonUpdateOk();
    }

    /**
     * Remove the specified class type.
     */
    public function destroy(ClassType $class_type): RedirectResponse
    {
        if ($class_type->hasClasses()) {
            return back()->with('flash_danger', 'This class type has classes attached to it. You cannot delete it.');
        }

        $class_type->delete();

        return back()->with('flash_success', __('msg.del_ok'));
    }
}
