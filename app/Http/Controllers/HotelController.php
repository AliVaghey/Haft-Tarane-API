<?php

namespace App\Http\Controllers;

use App\Http\Resources\HotelResource;
use App\Models\Hotel;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class HotelController extends Controller
{
    /**
     * get one hotel.
     */
    public function read(Hotel $hotel)
    {
        return new HotelResource($hotel);
    }

    /**
     * Get all the Hotels.
     */
    public function getAll(Request $request)
    {
        $results = Hotel::where("name", '!=', null);
        $results = $request->query('name') ? $results->where('name', $request->query('name')) : $results;
        $results = $request->query('country') ? $results->where('country', $request->query('country')) : $results;
        $results = $request->query('state') ? $results->where('state', $request->query('state')) : $results;
        $results = $request->query('city') ? $results->where('city', $request->query('city')) : $results;
        return HotelResource::collection($results->paginate(10));
    }

    /**
     * Get the authenticated admin hotels.
     */
    public function myHotels(Request $request)
    {
        $results = Hotel::where('admin_id', $request->user()->id);
        $results = $request->query('name') ? $results->where('name', $request->query('name')) : $results;
        $results = $request->query('country') ? $results->where('country', $request->query('country')) : $results;
        $results = $request->query('state') ? $results->where('state', $request->query('state')) : $results;
        $results = $request->query('city') ? $results->where('city', $request->query('city')) : $results;
        return HotelResource::collection($results->paginate(10));
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

        $hotel = Hotel::create([
            'admin_id' => $request->user()->id,
            'name' => $request->name,
            'address' => $request->address,
            'country' => $request->country,
            'state' => $request->state,
            'city' => $request->city,
            'gallery' => collect(),
        ]);

        return response(new HotelResource($hotel), 201);
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
     * Uploads 5 images to the storage and returns the paths.
     */
    public function uploadGallery(Request $request, $id)
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
            'photo_0' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],
            'photo_1' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],
            'photo_2' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],
            'photo_3' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],
            'photo_4' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],
        ]);

        $gallery = collect();
        for ($i = 0; $i < 5; $i++) {
            $name = 'photo_' . $i;
            if ($request->hasFile($name)) {
                $path = $request->$name->store('hotel-images');
                if ($hotel->gallery && $hotel->gallery->has($i)) {
                    Storage::delete($hotel->gallery->get($i));
                }
                $gallery->put($i, $path);
            }
        }
        $hotel->fill(['gallery' => $gallery])->save();

        return response()->noContent();
    }

    /**
     * Delete a Hotel.
     */
    public function delete($id)
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

    /**
     * Delete a photo from gallery.
     */
    public function deleteFromGallery($hotel_id, $photo_id)
    {
        if (!$hotel = Hotel::find($hotel_id)) {
            return response(['message' => __('exceptions.hotel-not-found')]);
        }
        try {
            Gate::authorize('isOwner', $hotel);
        } catch (AuthorizationException $exception) {
            return response(['message' => $exception->getMessage()], 403);
        }

        if ($hotel->gallery) {
            if ($hotel->gallery->has($photo_id)) {
                Storage::delete($hotel->gallery->get($photo_id));
                $hotel->gallery->forget($photo_id);
                $hotel->save();
            }
        }

        return response()->noContent();
    }
}
