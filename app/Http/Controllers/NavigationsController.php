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
            UserAccessType::Admin => env('FRONTEND_URL', 'http://localhost:3000') . '/admin-panel',
            UserAccessType::Agency => env('FRONTEND_URL', 'http://localhost:3000') . '/agency-panel',
            UserAccessType::User => env('FRONTEND_URL', 'http://localhost:3000') . '/user-panel',
        };
        return redirect($path);
    }
}
