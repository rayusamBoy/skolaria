<?php

namespace App\Repositories;

use App\Models\Lga;
use App\Models\Nationality;
use App\Models\State;
use Illuminate\Database\Eloquent\Collection;

class LocationRepo
{
    /*********** States ***************/

    /**
     * Get all states without ordering.
     */
    public function getStates(): Collection
    {
        return State::all();
    }

    /**
     * Get all states ordered by name ascending.
     */
    public function getAllStates(): Collection
    {
        return State::orderBy('name', 'asc')->get();
    }

    /**
     * Get states belonging to a given nationality (country).
     */
    public function getStatesByCountryId(int $country_id): Collection
    {
        return State::where('country_id', $country_id)
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * Get a single state by its ID.
     */
    public function getState(int $id): ?State
    {
        return State::find($id);
    }

    /*********** Nationalities ***************/

    /**
     * Get all nationalities ordered by name ascending.
     */
    public function getAllNationalities(): Collection
    {
        return Nationality::orderBy('name', 'asc')->get();
    }

    /**
     * Get a single nationality by its ID.
     */
    public function getNationalityById(int $id): ?Nationality
    {
        return Nationality::find($id);
    }

    /*********** LGAs ***************/

    /**
     * Get all LGAs/States for a given state, ordered by name ascending.
     */
    public function getStateById(int $state_id): Collection
    {
        return Lga::where('state_id', $state_id)
            ->orderBy('name', 'asc')
            ->get();
    }
}
