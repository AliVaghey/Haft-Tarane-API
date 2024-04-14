<?php

namespace App\Policies;

use App\Models\Support;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SupportPolicy
{
    /**
     * Determines whether User is the owner.
     */
    public function isOwner(User $user, Support $support)
    {
        return $user->id == $support->agency_id ?
            Response::allow() :
            Response::deny(__('exceptions.not-own-support'));
    }

    /**
     * Determines whether this supporter exists or not.
     */
    public function isRepeated(User $user, Support $support)
    {
        return $user->supports->filter(function ($sup) use ($support) {
            return $sup->name == $support->name;
        })->isEmpty()
        || $user->supports->filter(function ($sup) use ($support) {
            return $sup->phone == $support->phone;
        })->isEmpty() ?
            Response::allow() :
            Response::deny(__('exceptions.repeated-support'));
    }
}
