<?php

namespace App\Http\Controllers;

use App\Helpers\Usr;
use App\Models\Thread;
use App\Repositories\ExamRepo;
use App\Repositories\MyClassRepo;
use App\Repositories\PaymentRepo;
use App\Repositories\StudentRepo;
use App\Repositories\UserRepo;
use App\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use PermissionKit\Concerns\AuthorizesPermissions;

class SoftDeleteController extends Controller
{
    use AuthorizesPermissions;

    protected string $year;   // not actively used in this controller

    public function __construct(
        protected MyClassRepo $my_class,
        protected PaymentRepo $pay,
        protected UserRepo $user,
        protected ExamRepo $exam,
        protected StudentRepo $student
    ) {
        $this->year = Usr::getCurrentSession();
    }

    /**
     * Display the recycle bin with all trashed items.
     */
    public function bin(): View
    {
        $data['payments'] = $this->pay->getTrashedPayments();
        $data['users'] = $this->user->getTrashedUsers();
        $data['trashed_my_classes'] = $this->my_class->getTrashedClasses();
        $data['exams'] = $this->exam->getTrashed();
        $data['threads'] = Thread::getTrashed();

        return view('pages.bin', $data);
    }

    /**
     * Permanently delete all soft‑deleted records of a given model.
     */
    public function empty_soft_deleted_model(string $model_name): RedirectResponse
    {
        $allowed_models = Usr::getCanSoftDeleteModelNames();
        $model_name = strtolower($model_name);

        if (! \in_array($model_name, $allowed_models, true) || ! auth()->user()->hasRole(config('roles.default_roles.tenant.superadmin'))) {
            return back()->with('pop_error', __('msg.denied'));
        }

        if ($model_name === 'users') {
            // Delete user photos before force‑deleting
            $soft_deleted_users = User::onlyTrashed()->get();
            foreach ($soft_deleted_users as $user) {
                Usr::deleteUserPhotoDirs($user->roles->pluck('name'), $user->code);
            }
            User::onlyTrashed()->forceDelete();
        } else {
            Usr::getDbFacadesTable($model_name)->whereNotNull('deleted_at')->delete();
        }

        $model_name = str_replace('_', ' ', $model_name);

        return redirect()->back()->with('flash_success', ucfirst($model_name).' '.__('msg.force_del_ok'));
    }

    /********** Payments **********/

    /**
     * Restore a soft‑deleted payment.
     */
    public function payment_restore(int $id): RedirectResponse
    {
        $this->pay->restorePayment($id);

        return redirect()->route('bin')->with('flash_success', 'Payment '.__('msg.restore_ok'));
    }

    /**
     * Permanently delete a soft‑deleted payment.
     */
    public function payment_force_delete(int $id): RedirectResponse
    {
        $this->pay->forceDeletePayment($id);

        return redirect()->route('bin')->with('flash_success', 'Payment '.__('msg.force_del_ok'));
    }

    /********** Users **********/

    /**
     * Restore a soft‑deleted user.
     */
    public function user_restore(int $id): RedirectResponse
    {
        $this->user->restoreUser($id);

        return redirect()->route('bin')->with('flash_success', 'User '.__('msg.restore_ok'));
    }

    /**
     * Permanently delete a soft‑deleted user and their photos.
     */
    public function user_force_delete(int $id): RedirectResponse
    {
        $user = $this->user->findOnlyTrashed($id);
        $this->user->forceDeleteUser($id);
        Usr::deleteUserPhotoDirs($user->roles->pluck('name'), $user->code);

        return redirect()->route('bin')->with('flash_success', 'User '.__('msg.force_del_ok'));
    }

    /********** Classes **********/

    /**
     * Restore a soft‑deleted class.
     */
    public function my_class_restore(int $id): RedirectResponse
    {
        $this->my_class->restoreClass($id);

        return redirect()->route('bin')->with('flash_success', 'Class '.__('msg.restore_ok'));
    }

    /**
     * Permanently delete a soft‑deleted class.
     */
    public function my_class_force_delete(int $id): RedirectResponse
    {
        $this->my_class->forceDeleteClass($id);

        return redirect()->route('bin')->with('flash_success', 'Class '.__('msg.force_del_ok'));
    }

    /********** Exams **********/

    /**
     * Restore a soft‑deleted exam.
     */
    public function exam_restore(int $id): RedirectResponse
    {
        $this->exam->restore($id);

        return redirect()->route('bin')->with('flash_success', 'Exam '.__('msg.restore_ok'));
    }

    /**
     * Permanently delete a soft‑deleted exam.
     */
    public function exam_force_delete(int $id): RedirectResponse
    {
        $this->exam->forceDelete($id);

        return redirect()->route('bin')->with('flash_success', 'Exam '.__('msg.force_del_ok'));
    }

    /********** Message Threads **********/

    /**
     * Restore a soft‑deleted message thread.
     */
    public function thread_restore(int $id): RedirectResponse
    {
        Thread::restoreThread($id);

        return redirect()->route('bin')->with('flash_success', 'Message Thread '.__('msg.restore_ok'));
    }

    /**
     * Permanently delete a soft‑deleted message thread.
     */
    public function thread_force_delete(int $id): RedirectResponse
    {
        Thread::force_delete($id);

        return redirect()->route('bin')->with('flash_success', 'Message Thread '.__('msg.force_del_ok'));
    }
}
