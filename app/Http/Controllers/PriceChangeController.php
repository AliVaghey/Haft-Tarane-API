<?php

namespace App\Http\Controllers;

use App\Models\Costs;
use App\Models\Date;
use App\Models\PriceChange;
use Illuminate\Http\Request;

class PriceChangeController extends Controller
{
    public function add(Request $request, Date $date, Costs $cost)
    {
        if (PriceChange::where('date_id', $date->id)->where('cost_id', $cost->id)->get()->isNotEmpty()) {
            return response(['message' => __('exceptions.price-change-exists')], 403);
        }
        $request->validate([
            'price_change' => ['required', 'numeric'],
        ]);
        $price_change = PriceChange::create([
            'date_id' => $date->id,
            'cost_id' => $cost->id,
            'price_change' => $request->get('price_change'),
        ]);
        return response($price_change, 201);
    }

    public function delete(PriceChange $price_change)
    {
        $price_change->delete();
        return response()->noContent();
    }
}
