<?php

namespace App\Http\Controllers;

use App\Helpers\Usr;
use App\Http\Controllers\Controller;
use App\Models\Thread;
use App\Repositories\ExamRepo;
use App\Repositories\MyClassRepo;
use App\Repositories\PaymentRepo;
use App\Repositories\StudentRepo;
use App\Repositories\UserRepo;

class SoftDeleteController extends Controller
{
    protected $pay, $my_class, $user, $exm, $year, $student;

    public function __construct(MyClassRepo $my_class, PaymentRepo $pay, UserRepo $user, ExamRepo $exm, StudentRepo $student)
    {
        $this->permissionMap = config('permissions.names.tenant.soft_deletes');
        parent::__construct();
        $this->my_class = $my_class;
        $this->pay = $pay;
        $this->user = $user;
        $this->exm = $exm;
        $this->student = $student;
        $this->year = Usr::getCurrentSession();
    }

    public function bin()
    {
        $data['payments'] = $this->pay->getTrashed();
        $data['users'] = $this->user->getTrashed();
        $data['trashed_my_classes'] = $this->my_class->getTrashed();
        $data['exams'] = $this->exm->getTrashed();
        $data['threads'] = Thread::getTrashed();

        return view('pages.bin', $data);
    }

    public function empty_soft_deleted_model($model_name)
    {
        $can_soft_delete_models = Usr::getCanSoftDeleteModelNames();
        $model_name = strtolower($model_name);

        if (!\in_array($model_name, $can_soft_delete_models, true) || !auth()->user()->hasRole(config('roles.default_roles.tenant.superadmin')))
            return back()->with('pop_error', __('msg.denied'));

        if ($model_name === 'users') {
            $soft_deleted_users_query = $this->user->where([])->onlyTrashed();
            $soft_deleted_users = $soft_deleted_users_query->get();
            foreach ($soft_deleted_users as $user) {
                Usr::deleteUserPhotoDirs($user->roles->pluck('name'), $user->code);
            }
            $soft_deleted_users_query->forceDelete();
        } else {
            Usr::getDbFacadesTable($model_name)->whereNotNull("deleted_at")->delete();
        }

        $model_name = str_replace('_', ' ', $model_name);

        return redirect()->back()->with('flash_success', ucfirst($model_name) . ' ' . __('msg.force_del_ok'));
    }

    /** Payments */
    public function payment_restore($id)
    {
        $this->pay->restore($id);

        return redirect()->route('bin')->with('flash_success', 'Payments ' . __('msg.restore_ok'));
    }

    public function payment_force_delete($id)
    {
        $this->pay->forceDelete($id);

        return redirect()->route('bin')->with('flash_success', 'Payments ' . __('msg.force_del_ok'));
    }

    /** Users */
    public function user_restore($id)
    {
        $this->user->restore($id);

        return redirect()->route('bin')->with('flash_success', 'User ' . __('msg.restore_ok'));
    }

    public function user_force_delete($id)
    {
        $user = $this->user->findOnlyTrashed($id);

        $this->user->forceDelete($id);
        // Delete user's photo(s) from storage if exists
        Usr::deleteUserPhotoDirs($user->roles->pluck('name'), $user->code);

        return redirect()->route('bin')->with('flash_success', 'User ' . __('msg.force_del_ok'));
    }

    /** My Classes */
    public function my_class_restore($id)
    {
        $this->my_class->restore($id);

        return redirect()->route('bin')->with('flash_success', 'Class ' . __('msg.restore_ok'));
    }

    public function my_class_force_delete($id)
    {
        $this->my_class->forceDelete($id);

        return redirect()->route('bin')->with('flash_success', 'Class ' . __('msg.force_del_ok'));
    }

    /** Exams */
    public function exam_restore($id)
    {
        $this->exm->restore($id);

        return redirect()->route('bin')->with('flash_success', 'Exam ' . __('msg.restore_ok'));
    }

    public function exam_force_delete($id)
    {
        $this->exm->forceDelete($id);

        return redirect()->route('bin')->with('flash_success', 'Exam ' . __('msg.force_del_ok'));
    }

    /** Message Threads */
    public function thread_restore($id)
    {
        Thread::restoreThread($id);

        return redirect()->route('bin')->with('flash_success', 'Message Thread ' . __('msg.restore_ok'));
    }

    public function thread_force_delete($id)
    {
        Thread::force_delete($id);

        return redirect()->route('bin')->with('flash_success', 'Message Thread ' . __('msg.force_del_ok'));
    }
}
