<?php

namespace App\Http\Controllers;

use App\Http\Resources\CostResource;
use App\Http\Resources\SpecialTourResource;
use App\Models\Date;
use App\Models\SpecialTour;
use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SpecialTourController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function getAll()
    {
        return SpecialTourResource::collection(SpecialTour::all());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request, Tour $tour)
    {
        if (!$request->user()->isSuperAdmin()) {
            return response(['message' => __('not-allowed')], 403);
        }
        if (!$tour->isActive()) {
            return response(['message' => __('exceptions.not-active')], 403);
        }
        $request->validate([
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'importance' => 'nullable|min:0|max:255',
        ]);

        if (SpecialTour::where('tour_id', $tour->id)->get()->isNotEmpty()) {
            return response(['message' => 'این تور قبلا به عنوان تور خاص انتخاب شده است.'], 403);
        }

        $dates = collect(json_decode($request->get('dates'), true))->map(function ($date) {
            return Date::find($date);
        });
        foreach ($dates as $key => $date) {
            if ($date->expired) {
                $dates->forget($key);
            }
        }
        if ($dates->isEmpty()) {
            return response(['message' => "همه تاریخ های انتخاب شده منقضی شده اند."], 403);
        }

        $photo_path = $request->hasFile('photo') ? $request->file('photo')->store('special-tours', ['disk' => 'public']) : null;
        $model = SpecialTour::create([
            'tour_id' => $tour->id,
            'photo' => $photo_path,
            'importance' => $request->get('importance', 1),
            'advertisement' => $request->get('advertisement'),
            'dates' => $dates->map(function ($date) {
                return $date->id;
            })
        ]);

        return response(new SpecialTourResource($model), 201);
    }

    /**
     * Display the specified resource.
     */
    public function read(SpecialTour $tour)
    {
        return new SpecialTourResource($tour);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, SpecialTour $tour)
    {
        if (!$request->user()->isSuperAdmin()) {
            return response(['message' => __('not-allowed')], 403);
        }
        $request->validate([
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'importance' => 'nullable|min:0|max:255',
        ]);

        if ($request->hasFile('photo')) {
            $photo_path = $request->file('photo')->store('special-tours', ['disk' => 'public']);
            Storage::disk('public')->delete($tour->photo ?? '');
        } else {
            $photo_path = $tour->photo;
        }

        $dates = collect(json_decode($request->get('dates'), true))->map(function ($date) {
            return Date::find($date);
        });
        foreach ($dates as $key => $date) {
            if ($date->expired) {
                $dates->forget($key);
            }
        }
        if ($dates->isEmpty()) {
            return response(['message' => "همه تاریخ های انتخاب شده منقضی شده اند."], 403);
        }

        $tour->update([
            'photo' => $photo_path,
            'importance' => $request->get('importance') ?? $tour->importance,
            'advertisement' => $request->get('advertisement') ?? $tour->advertisement,
            'dates' => $dates->map(function ($date) {
                return $date->id;
            })
        ]);

        return response(new SpecialTourResource($tour), 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(Request $request, SpecialTour $tour)
    {
        if (!$request->user()->isSuperAdmin()) {
            return response(['message' => __('not-allowed')], 403);
        }
        $tour->removePhoto();
        $tour->delete();
        return response()->noContent();
    }

    public function getSpecialTourCosts(SpecialTour $tour)
    {
        return CostResource::collection($tour->tour->costs);
    }
}
