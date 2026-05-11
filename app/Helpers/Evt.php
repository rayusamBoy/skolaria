<?php

namespace App\Helpers;

use App\Models\Event;

class Evt
{
    /**
     * Get all events (unordered).
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAll()
    {
        return Event::all();
    }

    /**
     * Return the list of possible event statuses.
     *
     * @return array
     */
    public static function getStatusOptions(): array
    {
        return ['new', 'completed', 'cancelled', 'active'];
    }
}
