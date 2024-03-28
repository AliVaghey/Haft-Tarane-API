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
        return $user->isAdmin() ?
            Response::allow() :
            Response::deny(__('exceptions.not-admin'));
    }
}
