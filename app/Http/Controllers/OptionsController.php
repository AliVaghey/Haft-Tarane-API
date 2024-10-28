<?php

namespace App\Http\Controllers;

use App\Http\Resources\OptionResource;
use App\Models\Options;
use Illuminate\Http\Request;

class OptionsController extends Controller
{
    public function searchOptions(Request $request)
    {
        return $request->query('category') ?
            OptionResource::collection(Options::where('category', $request->query('category'))->get()) :
            [];
    }

    public function add(Request $request)
    {
        $request->validate([
            'category' => ['required', 'string'],
            'value' => ['required', 'string'],
        ]);
        $option = Options::create($request->only(['category', 'value']));

        return [
            'id' => $option->id,
            'category' => $option->category,
            'value' => $option->value,
        ];
    }

    public function remove(Options $option)
    {
        $option->delete();
        return response()->noContent();
    }

    public function setCurrencies(Request $request)
    {
        $request->validate([
            'usd' => ['required', 'integer'],
            'eur' => ['required', 'integer'],
            'aed' => ['required', 'integer']
        ]);

        $dollar = Options::firstOrCreate(['category' => 'USD-currency-unit'], ['value' => "0"])->first();
        $euro = Options::firstOrCreate(['category' => 'EUR-currency-unit'], ['value' => "0"])->first();
        $dirham = Options::firstOrCreate(['category' => 'AED-currency-unit'], ['value' => "0"])->first();

        $dollar->update(['value' => $request->usd]);
        $euro->update(['value' => $request->eur]);
        $dirham->update(['value' => $request->aed]);

        return [
            'usd' => $dollar->value,
            'eur' => $euro->value,
            'aed' => $dirham->value,
        ];
    }

    public function getCurrencies()
    {
        $dollar = Options::firstOrCreate(['category' => 'USD-currency-unit'], ['value' => "0"])->first();
        $euro = Options::firstOrCreate(['category' => 'EUR-currency-unit'], ['value' => "0"])->first();
        $dirham = Options::firstOrCreate(['category' => 'AED-currency-unit'], ['value' => "0"])->first();

        return [
            'usd' => (int)$dollar->value,
            'eur' => (int)$euro->value,
            'aed' => (int)$dirham->value,
        ];
    }
}
