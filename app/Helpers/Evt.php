<?php

namespace App\Helpers;

use App\Models\Event;
use Illuminate\Database\Eloquent\Collection;

class Evt
{
    /**
     * Get all events (unordered).
     *
     * @return Collection
     */
    public static function getAll()
    {
        return Event::all();
    }

    /**
     * Return the list of possible event statuses.
     */
    public static function getStatusOptions(): array
    {
        return ['new', 'completed', 'cancelled', 'active'];
    }
}
