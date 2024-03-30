<?php

namespace App\Http\Controllers;

use App\Http\Resources\PlaceResource;
use App\Models\Place;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Resources\Json\ResourceResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Access\AuthorizationException;

class PlaceController extends Controller
{
    /**
     * It lets admin users to create a new place/destination/city.
     */
    public function create(Request $request)
    {
        $user = $request->user();
        $request->validate(['name' => ['required', 'string']]);
        $place = Place::make([
            'name' => $request->name,
            'author' => $user->name,
        ]);

        try {
            Gate::authorize('isAdmin', $user);
            Gate::authorize('exists', $place);
        } catch (AuthorizationException $exception) {
            return response(['message' => $exception->getMessage()], 403);
        }

        $place->save();

        return response()->noContent();
    }

    /**
     * Get all the places. This function should be public.
     */
    public function getAllPlaces()
    {
        return PlaceResource::collection(Place::all());
    }

    /**
     * It lets admin users to delete a place.
     */
    public function deleteCity(Request $request, $id)
    {
        $user = $request->user();
        if (!$place = Place::find($id)) {
            return response(['message' => __('exceptions.place-not-found')]);
        }
        try {
            Gate::authorize('isAdmin', $user);
        } catch (AuthorizationException $exception) {
            return response(['message' => $exception->getMessage()], 403);
        }

        $place->delete();
        return response()->noContent();
    }

    /**
     * Edit a specific place.
     */
    public function edit(Request $request, string $id)
    {
        $user = $request->user();
        if (!$place = Place::find($id)) {
            return response(['message' => __('exceptions.place-not-found')]);
        }

        $place->fill([
            'name' => $request->name,
            'author' => $user->name,
        ]);

        try {
            Gate::authorize('isAdmin', $user);
            Gate::authorize('exists', $place);
        } catch (AuthorizationException $exception) {
            return response(['message' => $exception->getMessage()], 403);
        }

        $place->save();
        return response()->noContent();
    }
}
