<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): Response
    {
        $request->validate([
            'national_code' => ['nullable', 'numeric', 'regex:/^\d{10}$/'],
            'username' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'numeric', 'regex:/^(\+98|0)?9\d{9}$/', 'unique:' . User::class],
            'email' => ['nullable', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'username' => $request->username,
            'phone' => $request->phone,
            'email' => $request->email,
            'national_code' => $request->national_code,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return response()->noContent();
    }

    /**
     * Get the authenticated user info.
     */
    public function getInfo(Request $request)
    {
        return new UserResource($request->user());
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password:web'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);
        $user = Auth::user();

        $user->update([
            'password' => Hash::make($request->get('password')),
        ]);
    }

    /**
     * Update the authenticated user info.
     */
    public function updateInfo(Request $request)
    {
        $user = $request->user();
        $rules = [
            'national_code' => ['nullable', 'numeric', 'regex:/^\d{10}$/'],
            'gender' => ['nullable', 'string'],
            'first_name_fa' => ['nullable', 'string', 'max:255'],
            'last_name_fa' => ['nullable', 'string', 'max:255'],
            'first_name_en' => ['nullable', 'string', 'max:255'],
            'last_name_en' => ['nullable', 'string', 'max:255'],
        ];
        if ($request->phone != $user->phone) {
            $rules['phone'] = ['required', 'numeric', 'regex:/^(\+98|0)?9\d{9}$/', 'unique:' . User::class];
        }
        if ($request->email != $user->email) {
            $rules['email'] = ['nullable', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class];
        }
        $request->validate($rules);

        $user->fill([
            'national_code' => $request->national_code,
            'phone' => $request->phone,
            'gender' => $request->gender,
            'first_name_fa' => $request->first_name_fa,
            'last_name_fa' => $request->last_name_fa,
            'first_name_en' => $request->first_name_en,
            'last_name_en' => $request->last_name_en,
            'email' => $request->email,
        ])->save();

        return response()->noContent();
    }
}
