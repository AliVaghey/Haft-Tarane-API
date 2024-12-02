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
use Illuminate\Validation\Rule;

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
        $results = $request->query('name') ? $results->where('name', 'like', '%' . $request->query('name') . '%') : $results;
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
        $results = $request->query('name') ? $results->where('name', 'like', '%' . $request->query('name') . '%') : $results;
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
            'stars' => ['nullable', 'numeric', 'max:5', 'min:1'],
        ]);

        $hotel = Hotel::create([
            'admin_id' => $request->user()->id,
            'name' => $request->name,
            'address' => $request->address,
            'country' => $request->country,
            'state' => $request->state,
            'city' => $request->city,
            'gallery' => collect(),
            'stars' => $request->get('stars', 0)
        ]);

        return response($hotel, 201);
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
            'name' => ['required', 'string', 'max:255', Rule::unique('hotels', 'name')->ignore($hotel->id)],
            'address' => ['required', 'string'],
            'country' => ['nullable', 'string'],
            'state' => ['nullable', 'string'],
            'city' => ['nullable', 'string'],
            'stars' => ['nullable', 'numeric', 'max:5', 'min:1'],
        ]);

        $hotel->fill([
            'name' => $request->name,
            'address' => $request->address,
            'country' => $request->country,
            'state' => $request->state,
            'city' => $request->city,
            'stars' => $request->get('stars', 0)
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
            'photo_0' => ['nullable', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
            'photo_1' => ['nullable', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
            'photo_2' => ['nullable', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
            'photo_3' => ['nullable', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
            'photo_5' => ['nullable', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
            'photo_6' => ['nullable', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
            'photo_7' => ['nullable', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
            'photo_8' => ['nullable', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
            'photo_9' => ['nullable', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
        ]);

        $gallery = $hotel->gallery ?? collect();
        for ($i = 0; $i < 5; $i++) {
            $name = 'photo_' . $i;
            if ($request->hasFile($name)) {
                $path = $request->file($name)->store('hotel-images', ['disk' => 'public']);
                $gallery->push($path);
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
                Storage::disk('public')->delete($hotel->gallery->get($photo_id));
                $hotel->gallery->forget($photo_id);
                $hotel->save();
            }
        }

        return response()->noContent();
    }
}
