<?php

namespace App\Http\Controllers;

use App\Models\Ads;
use Illuminate\Http\Request;

class AdsController extends Controller
{
    public function all()
    {
        return Ads::all()->sortBy('sort');
    }

    public function read(Ads $ad)
    {
        return $ad;
    }

    public function create(Request $request)
    {
        $request->validate([
            'sort' => ['required', 'numeric', 'min:1', 'max:255'],
            'description' => ['nullable', 'string'],
            'link' => ['nullable', 'string'],
            'background_color' => ['nullable', 'string'],
            'text_color' => ['nullable', 'string'],
        ]);

        $sort = Ads::SortBy('sort')->last()->sort;

        $ad = Ads::create([
            'sort' => ++$sort,
            'link' => $request->get('link'),
            'description' => $request->get('description'),
            'background_color' => $request->get('background_color'),
            'text_color' => $request->get('text_color'),
        ]);

        return $ad;
    }

    public function update(Request $request, Ads $ad)
    {
        $request->validate([
            'sort' => ['required', 'numeric', 'min:1', 'max:255'],
            'description' => ['nullable', 'string'],
            'link' => ['nullable', 'string'],
            'background_color' => ['nullable', 'string'],
            'text_color' => ['nullable', 'string'],
        ]);

        $ad->update([
            'sort' => $request->get('sort', $ad->sort),
            'link' => $request->get('link', $ad->link),
            'description' => $request->get('description', $ad->description),
            'background_color' => $request->get('background_color', $ad->background_color),
            'text_color' => $request->get('text_color', $ad->text_color),
        ]);

        return $ad;
    }

    public function delete(Request $request, Ads $ad)
    {
        $ad->delete();
        return response()->noContent();
    }
}
