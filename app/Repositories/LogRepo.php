<?php

namespace App\Repositories;

use App\Models\Activity;
use App\Models\LoginHistory;

class LogRepo
{
    /*********** Activities ***************/

    public function getActivites(array $with = ['user'], string $orderBy = 'id', string $direction = 'desc')
    {
        return Activity::with($with)->orderBy($orderBy, $direction)->get();
    }

    public function deleteActivityLog(int $log_id)
    {
        return Activity::find($log_id)->delete();
    }

    /*********** Login History ***************/

    public function getLoginHistories(array $with = ['user'], string $orderBy = 'id', string $direction = 'desc')
    {
        return LoginHistory::with($with)->orderBy($orderBy, $direction)->get();
    }

    public function updateLoginHist(array $where, array $data)
    {
        return LoginHistory::where($where)->update($data);
    }
}
