<?php

namespace App\Http\Controllers;

use App\Enums\UserAccessType;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Query\JoinClause;
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
        $users = $request->query('admin') ? $user->where('access_type', 'admin') : $users;
        $users = $request->query('agency') ? $user->where('access_type', 'agency') : $users;
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
            AgencyInfoController::makeModel($user, $admin);
        }

        return response()->noContent();
    }

    /**
     * It allows admins to convert a user to agency or reverse.
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
        if ($user->access_type == UserAccessType::Agency && $user->agencyInfo->admin_id != $admin->id) {
            return response(['message' => __('exceptions.not-your-agency')], 403);
        }

        $request->validate(['access_type' => ['required', Rule::enum(UserAccessType::class)]]);
        if ($request->get('access_type') == UserAccessType::Admin->value || $request->get('access_type') == UserAccessType::SuperAdmin->value) {
            if ($admin->access_type != UserAccessType::SuperAdmin) {
                return response(['message' => __('exceptions.not-allowed')], 403);
            }
        }

        $user->fill(['access_type' => $request->access_type])->save();

        if ($request->access_type == UserAccessType::Agency->value) {
            AgencyInfoController::makeModel($user, $admin);
        }

        return response()->noContent();
    }

    public function adminDashboardInfo(Request $request)
    {
        $user = $request->user();

        $agency_count = DB::table('agency_infos')
            ->where('admin_id', $user->id)
            ->count();

        $today_sales = DB::table('tour_reservations')
            ->join('agency_infos', function (JoinClause $join) use ($user) {
                $join->on('tour_reservations.agency_id', '=', 'agency_infos.id')
                    ->where('agency_infos.admin_id', '=', $user->id);
            })
            ->whereBetween('tour_reservations.created_at', [now()->setTime(0, 0), now()->setTime(23, 59)])
            ->count();

        $month_sales = DB::table('tour_reservations')
            ->join('agency_infos', function (JoinClause $join) use ($user) {
                $join->on('tour_reservations.agency_id', '=', 'agency_infos.id')
                    ->where('agency_infos.admin_id', '=', $user->id);
            })
            ->whereBetween('tour_reservations.created_at', [now()->firstOfMonth(), now()->endOfMonth()])
            ->count();

        return [
            'admin_info' => new UserResource($user),
            'your_agency_count' => $agency_count,
            'today_sales' => $today_sales,
            'month_sales' => $month_sales,
        ];
    }
}
