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
}
