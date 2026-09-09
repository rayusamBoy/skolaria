<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Repositories\LogRepo;
use App\Repositories\MyClassRepo;
use App\Repositories\UserRepo;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LogController extends Controller
{
    protected UserRepo $user;

    protected MyClassRepo $my_class;

    protected LogRepo $log;

    public function __construct(UserRepo $user, MyClassRepo $my_class, LogRepo $log)
    {
        $this->permissionMap = config('permissions.names.central.central_logs');
        parent::__construct();
        $this->user = $user;
        $this->my_class = $my_class;
        $this->log = $log;
    }

    /**
     * Display central logs dashboard.
     */
    public function index(): View
    {
        $data['activities'] = $this->log->getActivities(['causer']);
        $data['login_histories'] = $this->log->getLoginHistories(['user'], 'logged_in_at', 'asc');
        $data['user_roles'] = $this->user->getRolesByName();

        return view('pages.central.logs', $data);
    }

    /**
     * Delete a specific activity log entry.
     */
    public function delete_activity($log_id): RedirectResponse
    {
        $this->log->deleteActivityById($log_id);

        return back()->with('flash_success', __('msg.del_ok'));
    }
}
