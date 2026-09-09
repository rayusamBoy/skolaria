<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Repositories\LogRepo;
use App\Repositories\MyClassRepo;
use App\Repositories\UserRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class LogController extends Controller
{
    protected UserRepo $user;

    protected MyClassRepo $my_class;

    protected LogRepo $log;

    public function __construct(UserRepo $user, MyClassRepo $my_class, LogRepo $log)
    {
        $this->permissionMap = config('permissions.names.tenant.logs');
        parent::__construct();
        $this->user = $user;
        $this->my_class = $my_class;
        $this->log = $log;
    }

    /**
     * Display login histories and activity logs.
     */
    public function index(): View
    {
        $data['activities'] = $this->log->getActivities(['causer'])->sortByDesc('id');
        $data['login_histories'] = $this->log->getLoginHistories(['user'], 'logged_in_at', 'asc');
        $data['user_roles'] = $this->user->getRolesByName();

        return view('pages.logs', $data);
    }

    /**
     * Delete an activity log entry.
     */
    public function delete_activity($log_id): RedirectResponse
    {
        $this->log->deleteActivityById($log_id);

        return back()->with('flash_success', __('msg.del_ok'));
    }

    /**
     * Clean the activity log (Artisan command).
     */
    public function clean_activity_log(): JsonResponse
    {
        $status = Artisan::call('activitylog:clean --force');

        $ok = $status === 0;
        $message = $ok
            ? 'All recorded activity older than '.config('activitylog.delete_records_older_than_days').' days deleted successfully.'
            : 'Activity log cleaning failed.';

        return Qs::json($message, $ok);
    }
}
