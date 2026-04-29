<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Repositories\LogRepo;
use App\Repositories\MyClassRepo;
use App\Repositories\UserRepo;
use Carbon\Carbon;
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
        $data['activities'] = $this->log->getActivites(['causer']);
        $data['login_histories'] = $this->log->getLoginHistories(['user'], 'last_login');
        $data['user_roles'] = $this->user->getRoles();

        return view('pages.central.logs', $data);
    }

    /**
     * Reset login history for a specific user.
     */
    public function reset_login_hist($user): RedirectResponse
    {
        $data = [
            'login_times' => 0,
            'last_reset' => Carbon::now(),
        ];

        $this->log->updateLoginHist(['user_id' => $user], $data);

        return back()->with('flash_success', __('msg.login_history_reset_success'));
    }

    /**
     * Delete a specific activity log entry.
     */
    public function delete_activity($log_id): RedirectResponse
    {
        $this->log->deleteActivityLog($log_id);

        return back()->with('flash_success', __('msg.del_ok'));
    }
}
