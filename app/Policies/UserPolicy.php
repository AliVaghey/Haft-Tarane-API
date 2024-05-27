<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determines whether the user is admin or not.
     */
    public function isAdmin(User $user)
    {
        return $user->isAdmin() || $user->isSuperAdmin() ?
            Response::allow() :
            Response::deny(__('exceptions.not-admin'));
    }

    /**
     * Determines whether the user is agency or not.
     */
    public function isAgency(User $user)
    {
        return $user->isAgency() || $user->isSuperAdmin() ?
            Response::allow() :
            Response::deny(__('exceptions.not-agency'));
    }
}
