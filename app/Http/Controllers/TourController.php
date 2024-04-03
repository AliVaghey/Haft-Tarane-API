<?php

namespace App\Http\Controllers;

use App\Enums\TourStatus;
use App\Http\Resources\TourResource;
use App\Models\Tour;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;

class TourController extends Controller
{
    /**
     * Create new tour.
     */
    public function create(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'trip_type' => ['required', 'string'],
            'expiration' => ['required', 'numeric'],
            'selling_type' => ['required', 'string'],
            'tour-styles' => ['nullable', 'json'],
            'evening_support' => ['required', 'boolean'],
            'midnight_support' => ['required', 'boolean'],
            'origin' => ['required', 'exists:places,name'],
            'destination' => ['required', 'exists:places,name'],
            'staying_nights' => ['required', 'numeric'],
            'transportation_type' => ['required', 'string'],
        ]);
        if ($request->midnight_support) {
            if (!$request->evening_support) {
                return response(['message' => __('exceptions.midnight-support-rule')]);
            }
        }

        Tour::create([
            'agency_id' => $request->user()->agencyInfo->id,
            'title' => $request->title,
            'trip_type' => $request->trip_type,
            'expiration' => $request->expiration,
            'selling_type' => $request->selling_type,
            'tour-styles' => collect(json_decode($request->tour_styles, true)),
            'evening_support' => $request->evening_support,
            'midnight_support' => $request->midnight_support,
            'origin' => $request->origin,
            'destination' => $request->destination,
            'staying_nights' => $request->staying_nights,
            'transportation_type' => $request->transportation_type,
            'status' => TourStatus::Draft,
        ]);

        return response()->noContent();
    }

    /**
     * Get the information of a tour.
     */
    public function read(Request $request, $id)
    {
        if (!$tour = Tour::find($id)) {
            return response(['message' => __('exceptions.tour-not-found')], 404);
        }
        try {
            Gate::authorize('isTourOwner', $tour);
        } catch (AuthorizationException $exception) {
            return response(['message' => $exception->getMessage()]);
        }
        return new TourResource($tour);
    }

    /**
     * Update a tour.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'trip_type' => ['required', 'string'],
            'expiration' => ['required', 'numeric'],
            'selling_type' => ['required', 'string'],
            'tour-styles' => ['nullable', 'json'],
            'evening_support' => ['required', 'boolean'],
            'midnight_support' => ['required', 'boolean'],
            'origin' => ['required', 'exists:places,name'],
            'destination' => ['required', 'exists:places,name'],
            'staying_nights' => ['required', 'numeric'],
            'transportation_type' => ['required', 'string'],
        ]);
        if ($request->midnight_support) {
            if (!$request->evening_support) {
                return response(['message' => __('exceptions.midnight-support-rule')]);
            }
        }

        if (!$tour = Tour::find($id)) {
            return response(['message' => __('exceptions.tour-not-found')], 404);
        }

        try {
            Gate::authorize('isTourOwner', $tour);
        } catch (AuthorizationException $exception) {
            return response(['message' => $exception->getMessage()]);
        }

        $tour->fill([
            'title' => $request->title,
            'trip_type' => $request->trip_type,
            'expiration' => $request->expiration,
            'selling_type' => $request->selling_type,
            'tour-styles' => collect(json_decode($request->tour_styles, true)),
            'evening_support' => $request->evening_support,
            'midnight_support' => $request->midnight_support,
            'origin' => $request->origin,
            'destination' => $request->destination,
            'staying_nights' => $request->staying_nights,
            'transportation_type' => $request->transportation_type,
            'status' => TourStatus::Draft,
        ])->save();

        return response()->noContent();
    }

    /**
     * Delete a tour.
     */
    public function delete(Request $request, $id)
    {
        if (!$tour = Tour::find($id)) {
            return response(['message' => __('exceptions.tour-not-found')], 404);
        }
        try {
            Gate::authorize('isTourOwner', $tour);
        } catch (AuthorizationException $exception) {
            return response(['message' => $exception->getMessage()]);
        }

        $tour->delete();
        return response()->noContent();
    }

    /**
     * Update or make Certificates for tour.
     */
    public function updateCertificate()
    {
        //TODO
    }
}
