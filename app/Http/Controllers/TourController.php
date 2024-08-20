<?php

namespace App\Http\Controllers;

use App\Enums\TourStatus;
use App\Http\Resources\AvailableToursResource;
use App\Http\Resources\CostResource;
use App\Http\Resources\SimilarDateResource;
use App\Http\Resources\TourListResource;
use App\Http\Resources\TourResource;
use App\Http\Resources\TourSearchResource;
use App\Models\Available;
use App\Models\certificate;
use App\Models\Costs;
use App\Models\Date;
use App\Models\Rejection;
use App\Models\Support;
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
            'tour_styles' => ['nullable', 'json'],
            'evening_support' => ['required', 'boolean'],
            'midnight_support' => ['required', 'boolean'],
            'origin' => ['required', 'exists:places,name'],
            'destination' => ['required', 'exists:places,name'],
            'staying_nights' => ['required', 'numeric'],
            'transportation_type' => ['required', 'string'],
            'support' => ['required', 'numeric', 'exists:supports,id'],
            'labels' => ['nullable', 'json']
        ]);
        if ($request->midnight_support) {
            if (!$request->evening_support) {
                return response(['message' => __('exceptions.midnight-support-rule')], 403);
            }
        }
        $agency_id = $request->user()->agencyInfo->id;
        $sup = Support::find($request->support);
        if ($sup->agency_id != $request->user()->id) {
            return response(['message' => __('exceptions.not-own-support')], 403);
        }

        $tour = Tour::create([
            'agency_id' => $agency_id,
            'title' => $request->title,
            'trip_type' => $request->trip_type,
            'expiration' => $request->expiration,
            'capacity' => $request->capacity,
            'selling_type' => $request->selling_type,
            'tour_styles' => collect(json_decode($request->tour_styles, true)),
            'evening_support' => $request->evening_support,
            'midnight_support' => $request->midnight_support,
            'origin' => $request->origin,
            'destination' => $request->destination,
            'staying_nights' => $request->staying_nights,
            'transportation_type' => $request->transportation_type,
            'status' => TourStatus::Draft,
            'hotels' => collect(),
            'support_id' => $sup->id,
            'labels' => collect(json_decode($request->get('labels'), true)),
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
            'tour_styles' => ['nullable', 'json'],
            'evening_support' => ['required', 'boolean'],
            'midnight_support' => ['required', 'boolean'],
            'origin' => ['required', 'exists:places,name'],
            'destination' => ['required', 'exists:places,name'],
            'staying_nights' => ['required', 'numeric'],
            'transportation_type' => ['required', 'string'],
            'support' => ['required', 'numeric', 'exists:supports,id'],
            'labels' => ['nullable', 'json'],
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

        $sup = Support::find($request->support);
        if ($sup->agency_id != $request->user()->id) {
            return response(['message' => __('exceptions.not-own-support')], 403);
        }

        $tour->fill([
            'title' => $request->title,
            'trip_type' => $request->trip_type,
            'expiration' => $request->expiration,
            'selling_type' => $request->selling_type,
            'tour_styles' => collect(json_decode($request->tour_styles, true)),
            'evening_support' => $request->evening_support,
            'midnight_support' => $request->midnight_support,
            'origin' => $request->origin,
            'destination' => $request->destination,
            'staying_nights' => $request->staying_nights,
            'transportation_type' => $request->transportation_type,
            'status' => TourStatus::Draft,
            'support_id' => $sup->id,
            'labels' => collect(json_decode($request->get('labels'), true)),
        ])->save();

        return response()->noContent();
    }

    /**
     * Delete a tour. //TODO: remove all dependencies
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

        //removing dependencies :
        if ($tour->certificate) {
            $tour->certificate->delete();
        }

        if ($tour->rejections->isNotEmpty()) {
            $tour->rejections->each(function ($rejection) {
                $rejection->delete();
            });
        }

        if ($tour->dates->isNotEmpty()) {
            $tour->dates->each(function ($date) {
                $date->delete();
            });
        }

        if ($tour->costs->isNotEmpty()) {
            $tour->costs->each(function ($cost) {
                $cost->delete();
            });
        }

        if ($tour->transportations->isNotEmpty()) {
            $tour->transportations->each(function ($t) {
                $t->delete();
            });
        }

        if ($tour->sysTransport->isNotEmpty()) {
            $tour->sysTransport->each(function ($t) {
                $t->delete();
            });
        }

        //removing the primary model :
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
            'tab_descriptions' => ['nullable', 'json'],
            'descriptions' => ['nullable', 'string'],
            'cancel_rules' => ['nullable', 'string'],
        ]);

        if (!$tour = Tour::find($request->tour_id)) {
            return response(['message' => __('exceptions.tour-not-found')], 404);
        }
        if ($tour->certificate) {
            $tour->certificate->update([
                'free_services' => collect(json_decode($request->free_services, true)),
                'certificates' => collect(json_decode($request->certificates, true)),
                'tab_descriptions' => collect(json_decode($request->tab_descriptions, true)),
                'descriptions' => $request->descriptions,
                'cancel_rules' => $request->cancel_rules,
            ]);
        } else {
            certificate::create([
                'tour_id' => $tour->id,
                'free_services' => collect(json_decode($request->free_services, true)),
                'certificates' => collect(json_decode($request->certificates, true)),
                'tab_descriptions' => collect(json_decode($request->tab_descriptions, true)),
                'descriptions' => $request->descriptions,
                'cancel_rules' => $request->cancel_rules,
            ]);
        }

        return response()->noContent();
    }

    /**
     * It adds date to a tour and puts it in pending status.
     */
    public function setPending(Request $request, $id)
    {
        if (!$tour = Tour::find($id)) {
            return response(['message' => __('exceptions.tour-not-found')], 404);
        }

        $request->validate(['profit_rate' => ['nullable', 'numeric', 'min:0', 'max:100']]);

        $tour->update([
            'status' => TourStatus::Pending,
            'profit_rate' => $request->get('profit_rate', 0)
        ]);
        $tour->refresh();
        AvailableController::deactive($tour);

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
        $tour->refresh();
        AvailableController::deactive($tour);
        return response()->noContent();
    }

    /**
     * Get all the active tours of the site.
     */
    public function activeTours(Request $request)
    {
        $results = Tour::where('status', 'active');
        if ($request->get('id') !== null) {
            return TourListResource::collection($results->where('id', $request->get('id'))->get());
        }

        $results = $request->query('origin') ? $results->where('origin', $request->query('origin')) : $results;
        $results = $request->query('destination') ? $results->where('destination', $request->query('destination')) : $results;
        $results = $request->query('title') ? $results->where('title', 'like','%' . $request->query('title') . '%') : $results;
        $results = $request->query('trip_type') ? $results->where('trip_type', $request->query('trip_type')) : $results;
        $results = $request->query('selling_type') ? $results->where('selling_type', $request->query('selling_type')) : $results;
        $results = $request->query('staying_nights') ? $results->where('staying_nights', $request->query('staying_nights')) : $results;
        $results = $request->query('transportation_type') ? $results->where('transportation_type', $request->query('transportation_type')) : $results;
        $results = $request->query('start') ? $results->where('start', $request->query('start')) : $results;
        $results = $request->query('end') ? $results->where('end', $request->query('end')) : $results;
        return TourListResource::collection($results->orderBy('updated_at', 'desc')->paginate(10));
    }

    public function activeToursNotPaginated(Request $request)
    {
        $results = Tour::where('status', 'active');
        if ($request->get('id') !== null) {
            return TourListResource::collection($results->where('id', $request->get('id'))->get());
        }

        $results = $request->query('origin') ? $results->where('origin', $request->query('origin')) : $results;
        $results = $request->query('destination') ? $results->where('destination', $request->query('destination')) : $results;
        $results = $request->query('title') ? $results->where('title', 'like','%' . $request->query('title') . '%') : $results;
        $results = $request->query('trip_type') ? $results->where('trip_type', $request->query('trip_type')) : $results;
        $results = $request->query('selling_type') ? $results->where('selling_type', $request->query('selling_type')) : $results;
        $results = $request->query('staying_nights') ? $results->where('staying_nights', $request->query('staying_nights')) : $results;
        $results = $request->query('transportation_type') ? $results->where('transportation_type', $request->query('transportation_type')) : $results;
        $results = $request->query('start') ? $results->where('start', $request->query('start')) : $results;
        $results = $request->query('end') ? $results->where('end', $request->query('end')) : $results;
        return TourListResource::collection($results->orderBy('updated_at', 'desc')->get());
    }

    /**
     * Get all the tours that belong to the agencies of admin.
     */
    public function adminMyTours(Request $request)
    {
        $results = Tour::where('status', 'active')
            ->join('agency_infos', function (JoinClause $join) use ($request) {
                $join->on('tours.agency_id', '=', 'agency_infos.id')
                    ->where('agency_infos.admin_id', '=', $request->user()->id);
            })
            ->select('tours.*');
        if ($request->query('id') !== null) {
            $results->where('tours.id', $request->query('id'));
        }

        return TourListResource::collection($results->orderBy('updated_at', 'desc')->paginate(10));
    }

    /**
     * It returns all the pending tours of agencies of the admin.
     */
    public function adminPendingTours(Request $request)
    {
        $results = Tour::where('status', 'pending')
            ->join('agency_infos', function (JoinClause $join) use ($request) {
                $join->on('tours.agency_id', '=', 'agency_infos.id')
                    ->where('agency_infos.admin_id', '=', $request->user()->id);
            })->select('tours.*');

        if ($request->query('id') !== null) {
            $results = $results->where('tours.id', $request->query('id'));
        }

        return TourListResource::collection($results->orderBy('updated_at', 'desc')->paginate(10));
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

        AvailableController::generate($tour);

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

        AvailableController::generate($tour);

        return response()->noContent();
    }

    /**
     * It returns all the rejection messages.
     */
    public function getMessages(Request $request, $id)
    {
        if (!$tour = Tour::find($id)) {
            return response(['message' => __('exceptions.tour-not-found')], 404);
        }
        try {
            Gate::authorize('isTourOwner', $tour);
        } catch (AuthorizationException $exception) {
            return response(['message' => $exception->getMessage()], 403);
        }

        return $tour->rejections ?
            $tour->rejections->map(function ($reject) {
                return ['id' => $reject->id, 'message' => $reject->message];
            }) : [];
    }

    /**
     * Get all the tours associated with the agency.
     */
    public function getTours(Request $request)
    {
        $results = $request->user()->agencyInfo->tours();
        if ($request->query('type')) {
            $results->where('status', $request->query('type'));
        }
        if ($request->query('id')) {
            $results->where('id', $request->query('id'));
        }
        return TourListResource::collection($results->orderBy('updated_at', 'desc')->paginate(10));
    }

    public function OLD_PublicGetTours(Request $request)
    {
        if ($request->query('all')) {
            return TourSearchResource::collection(
                Costs::join('tours', function (JoinClause $join) {
                    $join->on('costs.tour_id', '=', 'tours.id')
                        ->where('tours.status', '=', 'active')
                        ->join('dates', function (JoinClause $join) {
                            $join->on('tours.id', '=', 'dates.tour_id')
                                ->where('dates.expired', '=', false);
                        });
                    })
                ->select('costs.*')
                    ->distinct()
                ->orderBy("two_bed")
                ->paginate(10));
        }

        $results = Tour::where('status', 'active');
        if ($request->query('origin')) {
            $results->where('origin', $request->query('origin'));
        }
        if ($request->query('destination')) {
            $results->where('destination', $request->query('destination'));
        }
        $results = $results->get();
        foreach ($results as $key => $tour) {
            $f = false;
            foreach ($tour->dates as $date) {
                $start = new Carbon($date->start);
                if ($start->subDays($tour->expiration) > now()) {
                    $f = true;
                    break;
                }
            }
            if (!$f) {
                $results->forget($key);
            }
        }
        if ($request->query('start')) {
            $input = new Carbon ($request->query('start'));
            foreach ($results as $key => $tour) {
                $f = false;
                foreach ($tour->dates as $date) {
                    $start = new Carbon($date->start);
                    if ($start == $input && $start->subDays($tour->expiration) > now()) {
                        $f = true;
                    }
                }
                if (!$f) {
                    $results->forget($key);
                }
            }
        }
        $results = $results->map(function ($tour) {
            return $tour->costs;
        })->flatten(1);


        return $results->isNotEmpty() ? TourSearchResource::collection($results->sortByDesc('two_bed')) : [];
    }

    public function PublicGetTours(Request $request)
    {
        if ($request->query('all')) {
            return AvailableToursResource::collection(
                Available::where('expired', false)
                ->join('tours', function (JoinClause $join) {
                    $join->on('availables.tour_id', '=', 'tours.id')
                        ->where('tours.status', '=', 'active')
                        ->where('tours.transportation_type', '!=', 'hotel');
                })
                ->select('availables.*')
                ->orderBy('min_cost')
                ->paginate(10)
            );
        }

        $results = Available::where('availables.expired', false)
            ->join('tours', function (JoinClause $join) use ($request) {
                $join->on('availables.tour_id', '=', 'tours.id')
                    ->where('tours.status', '=', 'active')
                    ->where('tours.origin', '=', $request->query('origin'))
                    ->where('tours.destination', '=', $request->query('destination'))
                    ->where('tours.transportation_type', '!=', 'hotel');
            })
            ->join('dates', function (JoinClause $join) use ($request) {
                $join->on('availables.date_id', '=', 'dates.id')
                    ->where('dates.start', '=', $request->query('start'));
            })
            ->orderBy('min_cost')
            ->get();

        return AvailableToursResource::collection($results);
    }

    public function PublicGetHotelTours(Request $request)
    {
        if ($request->query('all')) {
            return AvailableToursResource::collection(
                Available::where('expired', false)
                    ->join('tours', function (JoinClause $join) {
                        $join->on('availables.tour_id', '=', 'tours.id')
                            ->where('tours.status', '=', 'active')
                            ->where('tours.transportation_type', '=', 'hotel');
                    })
                    ->select('availables.*')
                    ->orderBy('min_cost')
                    ->paginate(10)
            );
        }

        $results = Available::where('availables.expired', false)
            ->join('tours', function (JoinClause $join) use ($request) {
                $join->on('availables.tour_id', '=', 'tours.id')
                    ->where('tours.status', '=', 'active')
                    ->where('tours.origin', '=', $request->query('origin'))
                    ->where('tours.destination', '=', $request->query('destination'))
                    ->where('tours.transportation_type', '=', 'hotel');
            })
            ->join('dates', function (JoinClause $join) use ($request) {
                $join->on('availables.date_id', '=', 'dates.id')
                    ->where('dates.start', '=', $request->query('start'));
            })
            ->orderBy('min_cost')
            ->get();

        return AvailableToursResource::collection($results);
    }

    public function publicNatureTours(Request $request)
    {
        if ($request->query('all')) {
            return AvailableToursResource::collection(
                Available::where('expired', false)
                    ->join('tours', function (JoinClause $join) {
                        $join->on('availables.tour_id', '=', 'tours.id')
                            ->where('tours.status', '=', 'active')
                            ->where('tours.trip_type', '=', "طبیعت گردی");
                    })
                    ->select('availables.*')
                    ->orderBy('min_cost')
                    ->paginate(10)
            );
        }

        $results = Available::where('availables.expired', false)
            ->join('tours', function (JoinClause $join) use ($request) {
                $join->on('availables.tour_id', '=', 'tours.id')
                    ->where('tours.status', '=', 'active')
                    ->where('tours.origin', '=', $request->query('origin'))
                    ->where('tours.destination', '=', $request->query('destination'))
                    ->where('tours.transportation_type', '!=', 'hotel')
                    ->where('tours.trip_type', '=', "طبیعت گردی");
            })
            ->join('dates', function (JoinClause $join) use ($request) {
                $join->on('availables.date_id', '=', 'dates.id')
                    ->where('dates.start', '=', $request->query('start'));
            })
            ->orderBy('min_cost')
            ->get();

        return AvailableToursResource::collection($results);
    }

    public function getActiveTour(Tour $tour)
    {
        if (!$tour->isActive()) {
            return response(['message' => __('exceptions.tour-not-active')], 403);
        }
        return new TourResource($tour);
    }

    public function getCostInfo(Request $request, Costs $cost)
    {
        if ($cost->tour->status != TourStatus::Active) {
            return response(['message' => __('exceptions.tour-not-active')], 403);
        }
        $f = true;
        if ($cost->tour->isSysTrans()) {
            $date = Date::where('tour_id', $cost->tour->id)->where('start', $request->query('start'))->first();
            foreach ($cost->tour->sysTransport->where('date_id', $date->id) as $transport) {
                try {
                    $f1 = air_service()->checkFlightAvailability($transport->flight);
                } catch (\Exception $exception) {
                    return response($exception->getMessage(), 404);
                }
                if (!$f1) {
                    $f = false;
                    break;
                }
            }
        }
        if (!$f) {
            $date->expired = true;
            Available::where('cost_id', $cost->id)->where('date_id', $date->id)->update(['expired' => true]);
            return response(['message' => "پرواز های این تور پر شده اند."], 403);
        }
        return new CostResource($cost);
    }

    public function copy(Request $request, Tour $tour)
    {
        $new = $tour->replicate();
        $new->save();
        $new->refresh();

        //Certificates :
        if ($tour->certificate) {
            $new_certificate = $tour->certificate->replicate();
            $new_certificate->tour_id = $new->id;
            $new_certificate->save();
        }

        //Costs :
        foreach ($tour->costs as $cost) {
            $new_cost = $cost->replicate();
            $new_cost->tour_id = $new->id;
            $new_cost->save();
        }

        //Dates :
        if (!$tour->isSysTrans()) {
            foreach ($tour->dates as $date) {
                $new_date = $date->replicate();
                $new_date->tour_id = $new->id;
                $new_date->save();
            }
        }

        //Transportation :
        if (!$tour->isSysTrans()) {
            foreach ($tour->transportations as $transportation) {
                $new_transportation = $transportation->replicate();
                $new_transportation->tour_id = $new->id;
                $new_transportation->save();
            }
        }

        $new->status = TourStatus::Draft;
        $new->save();

        return response($new, 201);
    }

    public function similarDates(Request $request)
    {
        if($cost = Costs::find($request->query('cost_id'))) {
            return SimilarDateResource::collection($cost->tour->dates->where('expired', false));
        }
        return response([]);
    }

    public function closeDates(Request $request)
    {
         $results = Available::where('availables.expired', false)
             ->join('tours', function (JoinClause $join) use ($request) {
                    $join->on('availables.tour_id', '=', 'tours.id')
                        ->where('tours.status', '=', 'active')
                        ->where('tours.origin', '=', $request->query('origin'))
                        ->where('tours.destination', '=', $request->query('destination'));
         })
             ->join('dates', function (JoinClause $join) use ($request) {
                 $start = (new Carbon($request->query('start')))->subDays(5)->format('Y-m-d');
                 $end = (new Carbon($request->query('start')))->addDays(5)->format('Y-m-d');
                 $join->on('availables.date_id', '=', 'dates.id')
                    ->whereBetween('start', [$start, $end]);
             })
             ->orderBy('min_cost')
             ->get();

        return AvailableToursResource::collection($results);
    }

    public function getAll(Request $request)
    {
        $resualts = Tour::latest();
        if ($request->query('id')) {
            $resualts->where('id', $request->query('id'));
        }
        return $resualts->paginate(10);
    }

    public function getDrafts(Request $request)
    {
        $resualts = Tour::where('status', 'draft');
        if ($request->query('id')) {
            $resualts->where('id', $request->query('id'));
        }
        return $resualts->latest()->paginate(10);
    }

    public function getActives(Request $request)
    {
        $resualts = Tour::where('status', 'active');
        if ($request->query('id')) {
            $resualts->where('id', $request->query('id'));
        }
        return $resualts->latest()->paginate(10);
    }

    public function getExpired(Request $request)
    {
        $resualts = Tour::where('status', 'expired');
        if ($request->query('id')) {
            $resualts->where('id', $request->query('id'));
        }
        return $resualts->latest()->paginate(10);
    }

    public function getPending(Request $request)
    {
        $resualts = Tour::where('status', 'expired');
        if ($request->query('id')) {
            $resualts->where('id', $request->query('id'));
        }
        return $resualts->latest()->paginate(10);
    }

    public function getRejected(Request $request)
    {
        $resualts = Tour::where('status', 'expired');
        if ($request->query('id')) {
            $resualts->where('id', $request->query('id'));
        }
        return $resualts->latest()->paginate(10);
    }
}
