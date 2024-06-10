<?php

namespace App\Http\Controllers;

use App\Http\Resources\AirportResource;
use App\Models\Airport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AirportController extends Controller
{
    public function getAirports()
    {
        DB::table('airports')->delete();
        air_service()->getAirports();
    }

    public function allAirports()
    {
        return AirportResource::collection(Airport::all());
    }
}
