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
        try {
            air_service()->getAirports();
        } catch (\Exception $exception) {
            return response(['message' => $exception->getMessage()], 400);
        }
        return response(['message' => "اطلاعات با موفقیت ذخیره شد."]);
    }

    public function allAirports()
    {
        return AirportResource::collection(Airport::all());
    }
}
