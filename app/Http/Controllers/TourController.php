<?php

namespace App\Http\Controllers;

use App\Enums\TourStatus;
use App\Http\Resources\TourResource;
use App\Models\certificate;
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
            'capacity' => ['required', 'numeric'],
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
                return response(['message' => __('exceptions.midnight-support-rule')], 403);
            }
        }

        $tour = Tour::create([
            'agency_id' => $request->user()->agencyInfo->id,
            'title' => $request->title,
            'trip_type' => $request->trip_type,
            'expiration' => $request->expiration,
            'capacity' => $request->capacity,
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

        return new TourResource($tour);
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
            return response(['message' => $exception->getMessage()], 403);
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
                return response(['message' => __('exceptions.midnight-support-rule')], 403);
            }
        }

        if (!$tour = Tour::find($id)) {
            return response(['message' => __('exceptions.tour-not-found')], 404);
        }

        try {
            Gate::authorize('isTourOwner', $tour);
        } catch (AuthorizationException $exception) {
            return response(['message' => $exception->getMessage()], 403);
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
            return response(['message' => $exception->getMessage()], 403);
        }

        $tour->delete();
        return response()->noContent();
    }

    /**
     * Update or make Certificates for tour.
     */
    public function updateCertificate(Request $request)
    {
        $request->validate([
            'tour_id' => ['required', 'exists:tours,id'],
            'free_services' => ['nullable', 'json'],
            'certificates' => ['nullable', 'json'],
            'descriptions' => ['nullable', 'string'],
            'cancel_rules' => ['nullable', 'string'],
        ]);

        if (!$tour = Tour::find($request->tour_id)) {
            return response(['message' => __('exceptions.tour-not-found')], 404);
        }
        if ($tour->certificate) {
            $tour->certificate->fill([
                'free_services' => collect(json_decode($request->free_services, true)),
                'certificates' => collect(json_decode($request->certificates, true)),
                'descriptions' => $request->descriptions,
                'cancel_rules' => $request->cancel_rules,
            ]);
        } else {
            certificate::create([
                'tour_id' => $tour->id,
                'free_services' => collect(json_decode($request->free_services, true)),
                'certificates' => collect(json_decode($request->certificates, true)),
                'descriptions' => $request->descriptions,
                'cancel_rules' => $request->cancel_rules,
            ]);
        }

        return response()->noContent();
    }
}
