<?php

namespace App\Policies;

use App\Models\Tour;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TourPolicy
{
    /**
     * Checks to see whether the user is the owner of tour.
     */
    public function isTourOwner(User $user, Tour $tour)
    {
        return $user->agencyInfo->id == $tour->agency_id ?
            Response::allow() :
            Response::deny(__('exceptions.not-own-the-tour'));
    }

    /**
     * Checks to see if the user is the admin of the tour.
     */
    public function isTourAdmin(User $user, Tour $tour)
    {
        return $user->id == $tour->agency->admin->id ?
            Response::allow() :
            Response::deny(__('exceptions.not-own-the-tour'));
    }
}
