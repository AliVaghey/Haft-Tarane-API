<?php

namespace App\Http\Controllers;

use App\Http\Resources\AgencyInfoResource;
use App\Models\AgencyInfo;
use Illuminate\Http\Request;

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
            'zip_code' => ['nullable', 'numeric'],
            'web_site' => ['nullable', 'string', 'max:255'],
        ]);
        $input = [
            'name' => $request->name,
            'address' => $request->address,
            'c_phone' => $request->c_phone,
            'email' => $request->email,
            'zip_code' => $request->zip_code,
            'web_site' => $request->web_site,
        ];

        if ($info = $user->agencyInfo) {
            $info->fill($input)->save();
        } else {
            $input['user_id'] = $user->id;
            AgencyInfo::create($input);
        }

        return response()->noContent();
    }
}
