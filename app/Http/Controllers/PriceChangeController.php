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
            'price_change' => ['nullable', 'numeric'],
            'one_bed' => ['nullable', 'numeric'],
            'two_bed' => ['nullable', 'numeric'],
            'plus_one' => ['nullable', 'numeric'],
            'cld_6' => ['nullable', 'numeric'],
            'cld_2' => ['nullable', 'numeric'],
            'baby' => ['nullable', 'numeric'],
        ]);
        $price_change = PriceChange::create([
            'date_id' => $date->id,
            'cost_id' => $cost->id,
            'price_change' => $request->get('price_change'),
            'one_bed' => $request->get('one_bed'),
            'two_bed' => $request->get('two_bed'),
            'plus_one' => $request->get('plus_one'),
            'cld_6' => $request->get('cld_6'),
            'cld_2' => $request->get('cld_2'),
            'baby' => $request->get('baby'),
        ]);
        return response($price_change, 201);
    }

    public function addAll(Request $request, Date $date)
    {
        $request->validate([
            'price_change' => ['nullable', 'numeric'],
            'one_bed' => ['nullable', 'numeric'],
            'two_bed' => ['nullable', 'numeric'],
            'plus_one' => ['nullable', 'numeric'],
            'cld_6' => ['nullable', 'numeric'],
            'cld_2' => ['nullable', 'numeric'],
            'baby' => ['nullable', 'numeric'],
        ]);
        $prices = [];
        foreach ($date->tour->costs as $cost) {
            if (PriceChange::where('date_id', $date->id)->where('cost_id', $cost->id)->get()->isNotEmpty()) {
                return response(['message' => __('exceptions.price-change-exists')], 403);
            }
            $prices[] = PriceChange::create([
                'date_id' => $date->id,
                'cost_id' => $cost->id,
                'price_change' => $request->get('price_change'),
                'one_bed' => $request->get('one_bed'),
                'two_bed' => $request->get('two_bed'),
                'plus_one' => $request->get('plus_one'),
                'cld_6' => $request->get('cld_6'),
                'cld_2' => $request->get('cld_2'),
                'baby' => $request->get('baby'),
            ]);
        }
        return response($prices, 201);
    }

    public function update(Request $request, PriceChange $price_change)
    {
        $request->validate([
            'price_change' => ['nullable', 'numeric'],
            'one_bed' => ['nullable', 'numeric'],
            'two_bed' => ['nullable', 'numeric'],
            'plus_one' => ['nullable', 'numeric'],
            'cld_6' => ['nullable', 'numeric'],
            'cld_2' => ['nullable', 'numeric'],
            'baby' => ['nullable', 'numeric'],
        ]);
        $price_change->update($request->only([
            'price_change',
            'one_bed',
            'two_bed',
            'plus_one',
            'cld_6',
            'cld_2',
            'baby',
        ]));
        return response($price_change, 200);
    }

    public function delete(PriceChange $price_change)
    {
        $price_change->delete();
        return response()->noContent();
    }
}
