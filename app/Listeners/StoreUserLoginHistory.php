<?php

namespace App\Listeners;

use App\Events\UserLoggedIn;
use App\Models\LoginHistory;
use Carbon\Carbon;

class StoreUserLoginHistory
{
    /**
     * Handle the event.
     *
     * @param  UserLoggedIn  $event
     * @return void
     */
    public function handle(UserLoggedIn $event)
    {
        $user = $event->user;
        $current_timestamp = Carbon::now()->toDateTimeString();
        
        $saved_hist = LoginHistory::firstOrCreate(['user_id' => $user->id]);
        $saved_hist->update(['last_login' => $current_timestamp, 'login_count' => (int) $saved_hist->login_count + 1]);
    }
}
