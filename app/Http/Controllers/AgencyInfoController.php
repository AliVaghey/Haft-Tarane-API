<?php

namespace App\Http\Controllers;

use App\Enums\UserAccessType;
use App\Http\Resources\AgencyInfoResource;
use App\Http\Resources\AgencyResource;
use App\Http\Resources\UserResource;
use App\Models\AgencyInfo;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AgencyInfoController extends Controller
{
    /**
     * Get the agency info resource.
     */
    public function getInfo(Request $request)
    {
        return new AgencyInfoResource($request->user());
    }

    /**
     * It updates an agency info and if agency info doesn't exist It'll create a new one.
     */
    public function updateOrMake(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'c_phone' => ['required', 'numeric'],
            'email' => ['nullable', 'email'],
            'description' => ['nullable', 'string'],
            'instagram' => ['nullable', 'string'],
            'telegram' => ['nullable', 'string'],
            'whatsapp' => ['nullable', 'string'],
            'zip_code' => ['nullable', 'numeric'],
            'web_site' => ['nullable', 'string', 'max:255'],
        ]);
        $input = [
            'name' => $request->name,
            'address' => $request->address,
            'c_phone' => $request->c_phone,
            'email' => $request->email,
            'description' => $request->description,
            'instagram' => $request->instagram,
            'telegram' => $request->telegram,
            'whatsapp' => $request->whatsapp,
            'zip_code' => $request->zip_code,
            'web_site' => $request->web_site,
        ];

        if ($user->isSuperAdmin() && !$user->agencyInfo) {
            $input['admin_id'] = $user->id;
        }

        if ($info = $user->agencyInfo) {
            $info->fill($input)->save();
        } else {
            $input['user_id'] = $user->id;
            AgencyInfo::create($input);
        }

        return response()->noContent();
    }

    /**
     * Checks to see if an agency model exists, If not it'll make a new one.
     */
    static public function makeModel(User $user, User $admin)
    {
        if ($user->agencyInfo) {
            return;
        } else {
            AgencyInfo::create([
                'user_id' => $user->id,
                'admin_id' => $admin->id,
            ]);
        }
    }

    /**
     * It returns all the agencies for admin users.
     */
    public function getAll(Request $request)
    {
        return $request->query('name') ?
            AgencyResource::collection(AgencyInfo::where('name', $request->query('name'))->get()) :
            AgencyInfoResource::collection(User::where('access_type', 'agency')->paginate(10));
    }

    /**
     * It returns all the agencies that belong to the admin user.
     */
    public function getMyAgencies(Request $request)
    {
        $admin = $request->user();
        return $request->query('name') ?
            AgencyResource::collection($admin->agencies()->where('name', 'like', '%' . $request->query('name')) . '%') :
            AgencyResource::collection($admin->agencies()->paginate(10));
    }

    public function read(User $user)
    {
        return new AgencyInfoResource($user);
    }

    public function dashboardInfo(Request $request)
    {
        $user = $request->user();
        $agency = $user->agencyInfo;

        $today_sales = DB::table('tour_reservations')
            ->where('agency_id', $agency->id)
            ->whereBetween('created_at', [now()->setTime(0, 0), now()->setTime(23, 59, 59)])
            ->count();

        $month_sales = DB::table('tour_reservations')
            ->where('agency_id', $agency->id)
            ->whereBetween('created_at', [now()->firstOfMonth(), now()->lastOfMonth()])
            ->count();

        $all_sales = DB::table('tour_reservations')
            ->where('agency_id', $agency->id)
            ->count();

        $pending_sales = DB::table('tour_reservations')
            ->where('agency_id', $agency->id)
            ->where('status', 'pending')
            ->count();

        $active_tours = DB::table('tours')
            ->where('agency_id', $agency->id)
            ->where('status', 'active')
            ->count();

        $draft_tours = DB::table('tours')
            ->where('agency_id', $agency->id)
            ->where('status', 'draft')
            ->count();

        $rejected_tours =  DB::table('tours')
            ->where('agency_id', $agency->id)
            ->where('status', 'rejected')
            ->count();

        return [
            'agency_info' => new AgencyInfoResource($user),
            'admin' => new UserResource($user->agencyInfo->admin),
            'today_sales' => $today_sales,
            'month_sales' => $month_sales,
            'all_sales' => $all_sales,
            'pending_sales' => $pending_sales,
            'active_tours' => $active_tours,
            'draft_tours' => $draft_tours,
            'rejected_tours' => $rejected_tours,
        ];
    }
}
