<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use App\Models\Transportation;
use Illuminate\Http\Request;

class TransportationController extends Controller
{
    public function addTransport(Request $request, Tour $tour)
    {
        $request->validate([
            'type' => ['required', 'string'],
            'origin' => ['required', 'string'],
            'destination' => ['required', 'string'],
            'start' => ['required', 'string'],
            'end' => ['required', 'string'],
            'duration' => ['nullable', 'string'],
            'company_name' => ['nullable', 'string'],
            'transportation_type' => ['nullable', 'string'],
        ]);
        if ($tour->transportation_type == "system") {
            return response(['message' => __('exceptions.sys-transport')], 403);
        }

        $last_t = $tour->transportations->isNotEmpty() ? $tour->transportations->sortBy("sort")->last()->sort : 0;
        $transport = Transportation::create([
            'tour_id' => $tour->id,
            'sort' => ++$last_t,
            'type' => $request->get('type'),
            'origin' => $request->get('origin'),
            'destination' => $request->get('destination'),
            'start' => $request->get('start'),
            'end' => $request->get('end'),
            'duration' => $request->get('duration'),
            'company_name' => $request->get('company_name'),
            'transportation_type' => $request->get('transportation_type'),
        ]);

        return response($transport, 201);
    }

    public function deleteTransport(Request $request, Transportation $transportation)
    {
        $transportation->delete();
        return response()->noContent();
    }
}
