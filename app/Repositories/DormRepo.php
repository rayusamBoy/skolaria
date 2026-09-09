<?php

namespace App\Repositories;

use App\Models\Dorm;
use Illuminate\Database\Eloquent\Collection;

class DormRepo
{
    /*********** Dorms ***************/

    /**
     * Create a new dormitory.
     */
    public function createDorm(array $data): Dorm
    {
        return Dorm::create($data);
    }

    /**
     * Get all dormitories, ordered by a given column.
     *
     * @param  string  $order  Column to order by
     */
    public function getAllDorms(string $order = 'name'): Collection
    {
        return Dorm::orderBy($order)->get();
    }

    /**
     * Update a dormitory by ID.
     *
     * @return int Number of updated rows
     */
    public function updateDorm(int $id, array $data): int
    {
        return Dorm::where('id', $id)->update($data);
    }

    /**
     * Find a dormitory by its primary key.
     */
    public function findDorm(int $id): ?Dorm
    {
        return Dorm::find($id);
    }
}
