<?php

namespace App\Http\Controllers;

use App\Http\Resources\PlaceResource;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Access\AuthorizationException;

class PlaceController extends Controller
{
    /**
     * It lets user users to create a new place/destination/city.
     */
    public function create(Request $request)
    {
        $user = $request->user();
        $request->validate(['name' => ['required', 'string']]);
        $place = Place::make([
            'name' => $request->name,
            'author' => $user->username,
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

    public function read(Place $city)
    {
        return $city;
    }

    /**
     * Get all the places. This function should be public.
     */
    public function getAllPlaces(Request $request)
    {
        $result = $request->query('name') ?
            Place::where('name', 'like', '%' . $request->query('name') . '%')->paginate($request->query('per_page') ?? 10) :
            Place::paginate($request->query('per_page') ?? 10);
        return PlaceResource::collection($result);
    }

    /**
     * It lets user users to delete a place.
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
            'author' => $user->username,
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

    public function getAllTourOrigin(Request $request)
    {
        $result = DB::table('tours')
            ->where('status', 'active')
            ->where('transportation_type', '!=', 'hotel')
            ->select('origin')
            ->distinct()
            ->get();
        if ($request->query('name')) {
            $result = $result->where('name', '=', $request->query('name'));
        }
        return $result;
    }

    public function getAllTourDestination(Request $request)
    {
        return DB::table('tours')
            ->where('status', 'active')
            ->where('transportation_type', '!=', 'hotel')
            ->select('destination')
            ->distinct()
            ->get();
    }
}
