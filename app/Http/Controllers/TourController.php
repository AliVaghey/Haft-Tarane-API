<?php

namespace App\Http\Controllers;

use App\Enums\TourStatus;
use App\Http\Resources\TourResource;
use App\Models\certificate;
use App\Models\Rejection;
use App\Models\Tour;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
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
            'hotels' => collect(),
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

    /**
     * Link a hotel to the tour.
     */
    public function linkHotel(Request $request, $id)
    {
        $request->validate([
            'hotel_id' => ['required', 'exists:hotels,id'],
        ]);
        if (!$tour = Tour::find($id)) {
            return response(['message' => __('exceptions.tour-not-found')], 404);
        }
        if ($tour->hotels->search($request->hotel_id) === false) {
            $tour->hotels->push($request->hotel_id);
            $tour->save();
        } else {
            return response(['message' => __('exceptions.hotel-exists')], 403);
        }

        return response()->noContent();
    }

    /**
     * Unlink a hotel from the tour.
     */
    public function unlinkHotel(Request $request, $id)
    {
        $request->validate([
            'hotel_id' => ['required', 'exists:hotels,id'],
        ]);
        if (!$tour = Tour::find($id)) {
            return response(['message' => __('exceptions.tour-not-found')], 404);
        }
        $index = $tour->hotels->search($request->hotel_id);
        if ($index !== false) {
            $tour->hotels->forget($index);
            $tour->save();
        } else {
            return response(['message' => __('exceptions.hotel-not-selected')], 403);
        }

        return response()->noContent();
    }

    /**
     * It adds date to a tour and puts it in pending status.
     */
    public function addDateAndPending(Request $request, $id)
    {
        $request->validate([
            'start' => ['required', 'date'],
            'end' => ['required', 'date'],
        ]);
        if (!$tour = Tour::find($id)) {
            return response(['message' => __('exceptions.tour-not-found')], 404);
        }

        $start = new Carbon($request->start);
        $end = new Carbon($request->end);
        if ($end <= $start) {
            return response(['message' => __('exceptions.date-invalid')], 403);
        }

        $tour->fill([
            'start' => $start->format('Y-m-d'),
            'end' => $end->format('Y-m-d'),
            'status' => TourStatus::Pending,
        ])->save();

        return response()->noContent();
    }

    /**
     * It sets the status of tour to draft.
     */
    public function setToDraft(Request $request, $id)
    {
        if (!$tour = Tour::find($id)) {
            return response(['message' => __('exceptions.tour-not-found')], 404);
        }
        $tour->fill(['status' => TourStatus::Draft])->save();
        return response()->noContent();
    }

    /**
     * Get all the active tours of the site.
     */
    public function activeTours(Request $request)
    {
        $results = Tour::where('status', 'active');
        $results = $request->query('origin') ? $results->where('origin', $request->query('origin')) : $results;
        $results = $request->query('destination') ? $results->where('destination', $request->query('destination')) : $results;
        $results = $request->query('title') ? $results->where('title', $request->query('title')) : $results;
        $results = $request->query('trip_type') ? $results->where('trip_type', $request->query('trip_type')) : $results;
        $results = $request->query('selling_type') ? $results->where('selling_type', $request->query('selling_type')) : $results;
        $results = $request->query('staying_nights') ? $results->where('staying_nights', $request->query('staying_nights')) : $results;
        $results = $request->query('transportation_type') ? $results->where('transportation_type', $request->query('transportation_type')) : $results;
        $results = $request->query('start') ? $results->where('start', $request->query('start')) : $results;
        $results = $request->query('end') ? $results->where('end', $request->query('end')) : $results;
        return TourResource::collection($results->paginate(10));
    }

    /**
     * Get all the tours that belong to the agencies of admin.
     */
    public function adminMyTours(Request $request)
    {
        return TourResource::collection(Tour::where('status', 'active')
            ->join('agency_infos', function (JoinClause $join) use ($request) {
                $join->on('tours.agency_id', '=', 'agency_infos.id')
                    ->where('agency_infos.admin_id', '=', $request->user()->id);
            })
            ->select('tours.*')
            ->paginate(10));
    }

    /**
     * It returns all the pending tours of agencies of the admin.
     */
    public function adminPendingTours(Request $request)
    {
        return TourResource::collection(Tour::where('status', 'pending')
            ->join('agency_infos', function (JoinClause $join) use ($request) {
                $join->on('tours.agency_id', '=', 'agency_infos.id')
                    ->where('agency_infos.admin_id', '=', $request->user()->id);
            })->select('tours.*')
            ->paginate(10));
    }

    /**
     * It approves a tour and activates it.
     */
    public function approve(Request $request, $id)
    {
        if (!$tour = Tour::find($id)) {
            return response(['message' => __('exceptions.tour-not-found')], 404);
        }
        try {
            Gate::authorize('isTourAdmin', $tour);
        } catch (AuthorizationException $exception) {
            return response(['message' => $exception->getMessage()], 403);
        }

        $tour->status = TourStatus::Active;
        $tour->save();

        return response()->noContent();
    }

    /**
     * It creates a new rejection model with the given message and rejects the tour.
     */
    public function reject(Request $request, $id)
    {
        if (!$tour = Tour::find($id)) {
            return response(['message' => __('exceptions.tour-not-found')], 404);
        }
        try {
            Gate::authorize('isTourAdmin', $tour);
        } catch (AuthorizationException $exception) {
            return response(['message' => $exception->getMessage()], 403);
        }
        $request->validate(['message' => ['nullable', 'string']]);

        if ($request->message !== null) {
            Rejection::create([
                'tour_id' => $tour->id,
                'message' => $request->message,
            ]);
        }
        $tour->status = TourStatus::Rejected;
        $tour->save();

        return response()->noContent();
    }
}