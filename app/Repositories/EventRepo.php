<?php

namespace App\Repositories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Collection;

class EventRepo
{
    /*********** Events ***************/

    /**
     * Count all events in the database.
     *
     * @return int
     */
    public function countEvents(): int
    {
        return Event::count();
    }

    /**
     * Create a new event.
     *
     * @param array $data
     * @return Event
     */
    public function create(array $data): Event
    {
        return Event::create($data);
    }

    /**
     * Get events matching certain conditions.
     *
     * @param array $where
     * @return Collection
     */
    public function getEventsByConditions(array $where): Collection
    {
        return Event::where($where)->get();
    }

    /**
     * Get all events with their owning user.
     *
     * @return Collection
     */
    public function getAllEvents(): Collection
    {
        return Event::with('user')->get();
    }

    /**
     * Update an event by ID.
     *
     * @param int   $id
     * @param array $data
     * @return int  Number of updated rows
     */
    public function update(int $id, array $data): int
    {
        return Event::where('id', $id)->update($data);
    }

    /**
     * Delete an event by ID.
     *
     * @param int $id
     * @return int  Number of deleted rows
     */
    public function delete(int $id): int
    {
        return Event::destroy($id);
    }
}
