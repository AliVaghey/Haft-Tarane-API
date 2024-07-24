<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): Response
    {
        $request->authenticate();

        $request->session()->regenerate();

        return response()->noContent();
    }

    public function sendLogin(Request $request)
    {
        $code = rand(100000, 999999);
        $request->validate(['phone' => ['required', 'exists:users,phone']]);
        $request->session()->put('otp.phone', $request->get('phone'));
        $request->session()->put('otp.code', $code);
        $request->session()->put('otp.expires_at', now()->addMinutes(2));
        $request->session()->save();

        sms()->send(['CODE' => $code], 841406 , $request->get('phone'));

        return response()->noContent();
    }

    public function verifyLogin(Request $request)
    {
        $request->validate(['code' => ['required']]);
        if (
            $request->session()->has('otp.code') &&
            $request->session()->get('otp.expires_at') >= now()
        ) {
            $user = User::where('phone', $request->session()->get('otp.phone'))->first();
            Auth::login($user);
            $request->session()->forget('otp');
            $request->session()->save();
            $request->session()->regenerate();

            return response()->noContent();
        } else {
            $request->session()->forget('otp');
            $request->session()->save();
            $request->session()->regenerate();

            return response(['message' => __('exceptions.otp-expired')], 403);
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
