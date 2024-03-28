<?php

namespace App\Policies;

use App\Models\Place;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PlacePolicy
{
    /**
     * Checks to see whether the place already exists or not.
     */
    public function exists(Place $place)
    {
        return Place::where('name', $place->name)->get() ?
            Response::deny(__('exceptions.place-exists')) :
            Response::allow();
    }
}
