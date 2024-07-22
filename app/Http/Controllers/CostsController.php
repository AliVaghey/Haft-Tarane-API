<?php

namespace App\Http\Controllers;

use App\Models\Available;
use App\Models\Costs;
use App\Models\Hotel;
use App\Models\Tour;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CostsController extends Controller
{
    /**
     * It adds the cost model to the tour and hotel.
     */
    public function addCost(Request $request, $tour_id, $hotel_id)
    {
        if (!$tour = Tour::find($tour_id)) {
            return response(['message' => __('exceptions.tour-not-found')], 404);
        }
        if (!$hotel = Hotel::find($hotel_id)) {
            return response(['message' => __('exceptions.hotel-not-found')]);
        }
        $request->validate([
            'room_type' => ['required', 'string'],
            'one_bed' => ['required', 'numeric'],
            'two_bed' => ['required', 'numeric'],
            'plus_one' => ['required', 'numeric'],
            'cld_6' => ['required', 'numeric'],
            'cld_2' => ['required', 'numeric'],
            'baby' => ['required', 'numeric'],
        ]);

        $cost = Costs::create([
            'tour_id' => $tour->id,
            'hotel_id' => $hotel->id,
            'room_type' => $request->room_type,
            'one_bed' => $request->one_bed,
            'two_bed' => $request->two_bed,
            'plus_one' => $request->plus_one,
            'cld_6' => $request->cld_6,
            'cld_2' => $request->cld_2,
            'baby' => $request->baby,
        ]);

        return response($cost->toJson(), 201);
    }

    /**
     * Removes a Cost.
     */
    public function deleteCost(Request $request, $id)
    {
        if (!$cost = Costs::find($id)) {
            return response(['message' => __('exceptions.cost-not-found')], 404);
        }
        try {
            Gate::authorize('isTourOwner', $cost->tour);
        } catch (AuthorizationException $exception) {
            return response(['message' => $exception->getMessage()], 403);
        }

        $availables = Available::where('cost_id', $cost->id)->get();
        foreach ($availables as $available) {
            $available->delete();
        }

        $cost->delete();

        return response()->noContent();
    }

    public function updateCost(Request $request, Costs $cost)
    {
        $cost->update($request->all());
        return response($cost, 200);
    }
}
