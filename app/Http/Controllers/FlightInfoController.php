<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FlightInfoController extends Controller
{
    public function availableFlights(Request $request)
    {
        $request->validate([
            'from' => ['required', 'string'],
            'to' => ['required', 'string'],
            'date' => ['required', 'date'],
        ]);
        try {
            return air_service()->getAvailabeFlights(
                $request->get('from'),
                $request->get('to'),
                $request->get('date')
            );
        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], 400);
        }
    }
}
