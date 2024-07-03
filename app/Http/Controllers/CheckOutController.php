<?php

namespace App\Http\Controllers;

use App\Http\Resources\AgenciesCheckOutResource;
use App\Http\Resources\TourReservationResource;
use App\Models\AgencyInfo;
use App\Models\TourReservation;
use Illuminate\Http\Request;

class CheckOutController extends Controller
{
    public function getAgencies(Request $request)
    {
        return AgenciesCheckOutResource::collection($request->user()->agencies()->paginate(10));
    }

    public function getAgencySales(Request $request, AgencyInfo $agency)
    {
        return TourReservationResource::collection(
            TourReservation::where('agency_id', $agency->id)
                ->where('status', 'paid')
                ->paginate(
                    $request->query('per_page', 10)
                )
        );
    }
}
