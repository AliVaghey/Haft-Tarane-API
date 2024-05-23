<?php

namespace App\Http\Controllers;

use App\Http\Resources\SpecialTourResource;
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
        $request->validate([
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'importance' => 'nullable|min:0|max:255',
        ]);

        if (SpecialTour::where('tour_id', $tour->id)->get()->isNotEmpty()) {
            return response(['message' => 'این تور قبلا به عنوان تور خاص انتخاب شده است.'], 403);
        }

        $photo_path = $request->hasFile('photo') ? $request->file('photo')->store('special-tours', ['disk' => 'public']) : null;
        $model = SpecialTour::create([
            'tour_id' => $tour->id,
            'photo' => $photo_path,
            'importance' => $request->get('importance', 1),
            'advertisement' => $request->get('advertisement'),
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
        $request->validate([
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'importance' => 'nullable|min:0|max:255',
        ]);

        if ($request->hasFile('photo')) {
            $photo_path = $request->file('photo')->store('special-tours', ['disk' => 'public']);
            Storage::disk('public')->delete($tour->photo);
        } else {
            $photo_path = $tour->photo;
        }

        $tour->update([
            'photo' => $photo_path,
            'importance' => $request->get('importance') ?? $tour->importance,
            'advertisement' => $request->get('advertisement') ?? $tour->advertisement,
        ]);

        return response(new SpecialTourResource($tour), 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(SpecialTour $tour)
    {
        $tour->removePhoto();
        $tour->delete();
        return response()->noContent();
    }
}
