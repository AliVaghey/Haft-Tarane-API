<?php

namespace App\Http\Controllers;

use App\Models\Date;
use App\Models\Tour;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DateController extends Controller
{
    /**
     * Add a date to a tour.
     */
    public function addDate(Request $request, $id)
    {
        if (!$tour = Tour::find($id)) {
            return response(['message' => __('exceptions.tour-not-found')], 404);
        }
        try {
            Gate::authorize('isTourOwner', $tour);
        } catch (AuthorizationException $exception) {
            return response(['message' => $exception->getMessage()], 403);
        }

        $request->validate([
            'start' => ['required', 'date'],
            'end' => ['required', 'date'],
        ]);
        $start = new Carbon($request->start);
        $end = new Carbon($request->end);
        if ($end <= $start) {
            return response(['message' => __('exceptions.date-invalid')], 403);
        }
        foreach ($tour->dates as $date) {
            if ($date->start == $request['start'] && $date->end == $request['end']) {
                return response(['message' => __('exceptions.date-exists')], 403);
            }
        }

        $date = Date::create([
            'tour_id' => $tour->id,
            'start' => $start->format('Y-m-d'),
            'end' => $end->format('Y-m-d'),
        ]);

        return response($date->toArray(), 201);
    }

    /**
     * Delete a date from a tour.
     */
    public function deleteDate(Request $request, $id)
    {
        if (!$date = Date::find($id)) {
            return response(['message' => __('exceptions.date-not-found')], 404);
        }

        $date->delete();
        return response()->noContent();
    }
}