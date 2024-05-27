<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Hotel;
use Illuminate\Auth\Access\Response;

class HotelPolicy
{
    /**
     * Checks to see where the user is the owner of the hotel or not.
     */
    public function isOwner(User $user, Hotel $hotel)
    {
        return $user->id == $hotel->admin_id || $user->isSuperAdmin() ?
            Response::allow() :
            Response::deny(__('exceptions.not-own-hotel'));
    }
}
