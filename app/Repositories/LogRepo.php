<?php

namespace App\Repositories;

use App\Models\Activity;
use App\Models\LoginHistory;
use Illuminate\Database\Eloquent\Collection;

class LogRepo
{
    /*********** Activities ***************/

    /**
     * Get activities with optional eager‑loaded relations, ordered by a given column.
     *
     * @param array  $with      Relations to eager load
     * @param string $orderBy   Column to sort by
     * @param string $direction 'asc' or 'desc'
     * @return Collection
     */
    public function getActivities(array $with = ['user'], string $orderBy = 'id', string $direction = 'desc'): Collection
    {
        return Activity::with($with)->orderBy($orderBy, $direction)->get();
    }

    /**
     * Delete an activity log entry by its primary key.
     *
     * @param int $id
     * @return bool|null
     */
    public function deleteActivityById(int $id): ?bool
    {
        return Activity::find($id)?->delete();
    }

    /*********** Login History ***************/

    /**
     * Get login histories with optional eager‑loaded relations, ordered by a given column.
     *
     * @param array  $with
     * @param string $orderBy
     * @param string $direction
     * @return Collection
     */
    public function getLoginHistories(array $with = ['user'], string $orderBy = 'id', string $direction = 'desc'): Collection
    {
        return LoginHistory::with($with)->orderBy($orderBy, $direction)->get();
    }

    /**
     * Update login histories matching the given conditions.
     *
     * @param array $where  Conditions
     * @param array $data   Values to update
     * @return int          Number of updated rows
     */
    public function updateLoginHistory(array $where, array $data): int
    {
        return LoginHistory::where($where)->update($data);
    }
}
