<?php

namespace App\Http\Controllers;

use App\Enums\UserAccessType;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * It returns a list of users for admins.
     */
    public function getUserList(Request $request)
    {
        $user = $request->user();
        try {
            Gate::authorize('isAdmin', $user);
        } catch (AuthorizationException $exception) {
            return response(['message' => $exception->getMessage()], 403);
        }

        $users = DB::table('users');
        $users = $request->query('username') ? $users->where('username', $request->query('username')) : $users;
        $users = $request->query('phone') ? $users->where('phone', $request->query('phone')) : $users;

        return UserResource::collection($users->paginate(10));
    }

    /**
     * Get the information of a single user by user users.
     */
    public function getUser(Request $request, $id)
    {
        $admin = $request->user();
        try {
            Gate::authorize('isAdmin', $admin);
        } catch (AuthorizationException $exception) {
            return response(['message' => $exception->getMessage()], 403);
        }
        if (!$user = User::find($id)) {
            return response(['message' => __('exceptions.user-not-found')], 404);
        }

        return new UserResource($user);
    }

    /**
     * Update a user information by user.
     */
    public function updateUser(Request $request, $id)
    {
        $admin = $request->user();
        try {
            Gate::authorize('isAdmin', $admin);
        } catch (AuthorizationException $exception) {
            return response(['message' => $exception->getMessage()], 403);
        }
        if (!$user = User::find($id)) {
            return response(['message' => __('exceptions.user-not-found')], 404);
        }
        $rules = [
            'username' => ['required', 'string', 'max:255'],
            'access_type' => ['required', Rule::enum(UserAccessType::class)],
            'national_code' => ['nullable', 'numeric', 'regex:/^\d{10}$/'],
        ];
        $rules['phone'] = $request->phone == $user->phone ? [] : ['required', 'numeric', 'regex:/^(\+98|0)?9\d{9}$/', 'unique:' . User::class];
        $rules['email'] = $request->email == $user->email ? [] : ['nullable', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class];
        $request->validate($rules);

        $user->fill([
            'username' => $request->username,
            'phone' => $request->phone,
            'email' => $request->email,
            'access_type' => $request->access_type,
            'national_code' => $request->national_code,
        ])->save();

        if ($request->access_type == UserAccessType::Agency->value) {
            AgencyInfoController::makeModel($user);
        }

        return response()->noContent();
    }

    /**
     * It modifies the access_type of a user by user.
     */
    public function changeAccess(Request $request, $id)
    {
        $admin = $request->user();
        try {
            Gate::authorize('isAdmin', $admin);
        } catch (AuthorizationException $exception) {
            return response(['message' => $exception->getMessage()], 403);
        }
        if (!$user = User::find($id)) {
            return response(['message' => __('exceptions.user-not-found')], 404);
        }

        $request->validate(['access_type' => ['required', Rule::enum(UserAccessType::class)]]);
        $user->fill(['access_type' => $request->access_type])->save();

        if ($request->access_type == UserAccessType::Agency->value) {
            AgencyInfoController::makeModel($user);
        }

        return response()->noContent();
    }
}
