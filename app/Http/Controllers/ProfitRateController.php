<?php

namespace App\Http\Controllers;

use App\Models\ProfitRate;
use Illuminate\Http\Request;

class ProfitRateController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $profitRate = ProfitRate::create($request->only(['name', 'rate']));

        return response($profitRate, 201);
    }

    public function edit(Request $request, ProfitRate $rate)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $rate->update($request->only(['name', 'rate']));

        return response($rate, 200);
    }

    public function delete(Request $request, ProfitRate $rate)
    {
        $rate->delete();

        return response()->noContent();
    }

    public function all()
    {
        return ProfitRate::all();
    }

    public function read(ProfitRate $rate)
    {
        return response($rate, 200);
    }
}
