<?php

namespace App\Http\Controllers;

use App\Enums\UserAccessType;
use Illuminate\Http\Request;

class NavigationsController extends Controller
{
    /**
     * It decides the right panel for the user base on their type.
     */
    public function redirectPanel(Request $request)
    {
        $path = match ($request->user()->access_type) {
            UserAccessType::Admin => '/admin-panel',
            UserAccessType::Agency => '/agency-panel',
            UserAccessType::User => '/user-panel',
        };
        return redirect($path);
    }
}
