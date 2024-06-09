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
            'start' => ['required', 'date'],
            'end' => ['required', 'date'],
            'duration' => ['nullable', 'numeric', 'min:0', 'max:255'],
            'company_name' => ['nullable', 'string'],
            'transportation_type' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric'],
        ]);

        $last_t = $tour->transportations->isNotEmpty() ? $tour->transportations->sortBy("sort")->last()->sort : 0;
        $transport = Transportation::create([
            'tour_id' => $tour->id,
            'sort' => ++$last_t,
            'type' => $request->get('type'),
            'origin' => $request->get('origin'),
            'destination' => $request->get('destination'),
            'start' => $request->get('start'),
            'end' => $request->get('end'),
            'price' => $request->get('price'),
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
