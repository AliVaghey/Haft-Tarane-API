<?php

namespace App\Http\Controllers;

use App\Enums\Currency;
use App\Models\Costs;
use App\Models\Date;
use App\Models\PriceChange;
use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
            'currency' => ['required', Rule::enum(Currency::class)]
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
            'currency' => $request->currency
        ]);
        AvailableController::update($date, $cost);
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
            'currency' => ['required', Rule::enum(Currency::class)]
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
                'currency' => $request->currency
            ]);
        }
        AvailableController::generate($date->tour);
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
            'currency' => ['required', Rule::enum(Currency::class)]
        ]);
        $price_change->update($request->only([
            'price_change',
            'one_bed',
            'two_bed',
            'plus_one',
            'cld_6',
            'cld_2',
            'baby',
            'currency'
        ]));
        return response($price_change, 200);
    }

    public function delete(PriceChange $price_change)
    {
        $price_change->delete();
        return response()->noContent();
    }

    public function deleteAll(Request $request, Tour $tour)
    {
        foreach ($tour->dates as $date) {
            PriceChange::where('date_id', $date->id)->delete();
        }
        AvailableController::generate($tour);
        return response(null, 204);
    }
}
