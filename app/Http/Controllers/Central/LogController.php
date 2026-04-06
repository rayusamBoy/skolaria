<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Repositories\LogRepo;
use App\Repositories\MyClassRepo;
use App\Repositories\UserRepo;
use Carbon\Carbon;

class LogController extends Controller
{
    protected $user, $my_class, $log;

    public function __construct(UserRepo $user, MyClassRepo $my_class, LogRepo $log)
    {
        $this->permissionMap = config('permissions.names.central.central_logs');
        parent::__construct();
        $this->user = $user;
        $this->my_class = $my_class;
        $this->log = $log;
    }

    public function index()
    {
        $d['activities'] = $this->log->getActivites()->sortBy('id', SORT_REGULAR, TRUE);
        $d['login_histories'] = $this->log->getLoginHistories()->sortBy('last_login', SORT_REGULAR, TRUE);
        $d['user_roles'] = $this->user->getRoles();

        return view('pages.central.logs', $d);
    }

    public function reset_login_hist($user)
    {
        $current_timestamp = Carbon::now()->toDateTimeString();
        $data = ['login_times' => 0, 'last_reset' => $current_timestamp, 'last_login' => NULL];

        $this->log->updateLoginHist(['user_id' => $user], $data);

        return back()->with('flash_success', __('msg.login_history_reset_success'));
    }

    public function delete_activity($log_id)
    {
        $this->log->deleteActivityLog($log_id);

        return back()->with('flash_success', __('msg.del_ok'));
    }
}
