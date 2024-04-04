<?php

namespace App\Http\Controllers;

use App\Http\Resources\HotelResource;
use App\Models\Hotel;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class HotelController extends Controller
{
    /**
     * Get all the Hotels.
     */
    public function getAll(Request $request)
    {
        $results = Hotel::all();
        $results = $request->query('name') ? $results->where('name', $request->query('name')) : $results;
        $results = $request->query('country') ? $results->where('country', $request->query('country')) : $results;
        $results = $request->query('state') ? $results->where('state', $request->query('state')) : $results;
        $request = $request->query('city') ? $results->where('city', $request->query('city')) : $results;
        return HotelResource::collection($results);
    }

    /**
     * Get the authenticated admin hotels.
     */
    public function myHotels(Request $request)
    {
        $results = $request->user()->hotels;
        $results = $request->query('name') ? $results->where('name', $request->query('name')) : $results;
        $results = $request->query('country') ? $results->where('country', $request->query('country')) : $results;
        $results = $request->query('state') ? $results->where('state', $request->query('state')) : $results;
        $request = $request->query('city') ? $results->where('city', $request->query('city')) : $results;
        return HotelResource::collection($results);
    }

    /**
     * Create a new hotel.
     */
    public function create(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:hotels,name'],
            'address' => ['required', 'string'],
            'country' => ['nullable', 'string'],
            'state' => ['nullable', 'string'],
            'city' => ['nullable', 'string'],
        ]);

        Hotel::create([
            'admin_id' => $request->user()->id,
            'name' => $request->name,
            'address' => $request->address,
            'country' => $request->country,
            'state' => $request->state,
            'city' => $request->city,
        ]);

        return response()->noContent();
    }

    /**
     * Update/edit an existing hotel.
     */
    public function edit(Request $request, $id)
    {
        if (!$hotel = Hotel::find($id)) {
            return response(['message' => __('exceptions.hotel-not-found')]);
        }
        try {
            Gate::authorize('isOwner', $hotel);
        } catch (AuthorizationException $exception) {
            return response(['message' => $exception->getMessage()], 403);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:hotels,name'],
            'address' => ['required', 'string'],
            'country' => ['nullable', 'string'],
            'state' => ['nullable', 'string'],
            'city' => ['nullable', 'string'],
        ]);
        $hotel->fill([
            'name' => $request->name,
            'address' => $request->address,
            'country' => $request->country,
            'state' => $request->state,
            'city' => $request->city,
        ])->save();

        return response()->noContent();
    }

    /**
     * Delete a Hotel.
     */
    public function delete(Request $request, $id)
    {
        if (!$hotel = Hotel::find($id)) {
            return response(['message' => __('exceptions.hotel-not-found')]);
        }
        try {
            Gate::authorize('isOwner', $hotel);
        } catch (AuthorizationException $exception) {
            return response(['message' => $exception->getMessage()], 403);
        }

        $hotel->delete();

        return response()->noContent();
    }
}
