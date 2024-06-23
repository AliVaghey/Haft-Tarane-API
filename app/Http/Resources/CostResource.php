<?php

namespace App\Http\Resources;

use App\Models\Hotel;
use App\Models\SysTransport;
use App\Models\Tour;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $tour = $this->tour;
        $date = $this->findDate($tour, $request->query('start'));

        return [
            'tour_id' => $tour->id,
            'tour_name' => $tour->title,
            'capacity' => $tour->capacity,
            'trip_type' => $tour->trip_type,
            'tour_styles' => $tour->tour_styles,
            'evening_support' => $tour->evening_support,
            'midnight_support' => $tour->midnight_support,
            'origin' => $tour->origin,
            'destination' => $tour->destination,
            'staying_nights' => $tour->staying_nights,
            'agency_name' => $tour->agency->name,
            'min_cost' => $this->minCost($tour, $date),
            'hotel' => $this->getHotel($this->hotel),
            'transportation' => $tour->isSysTrans() ? $this->getSysTrans($tour, $date) : $tour->transportations->sortBy("sort"),
            'cost' => parent::toArray($request),
            'date' => $this->findDate($tour, $request->query('start')),
            'certificate' => $tour->certificate,
        ];
    }

    public function minCost(Tour $tour, $date)
    {
        if ($tour->isSysTrans()) {
            $price = $this->two_bed;
            foreach (SysTransport::where('date_id', $date[0]['id'])->get() as $transport) {
                $price += ($transport->flight->price_final / 10);
            }
            return $price;
        } else {
            return $this->two_bed;
        }
    }

    public function getSysTrans(Tour $tour, $date)
    {
        $result = [];
        foreach ($tour->sysTransport->where('date_id', $date['id']) as $transport) {
            $result [] = [
                'transportation_id' => $transport->id,
                'flight' => $transport->flight
            ];
        }
        return $result;
    }

    public function findDate(Tour $tour, $input = null)
    {
        if ($input) {
            $input = new Carbon($input);
        }
        $date = null;
        if ($input) {
            foreach ($tour->dates as $d) {
                $start = new Carbon($d->start);
                if ($input == $start) {
                    $date = $d;
                    break;
                }
            }
        } else {
            $date = $tour->dates->map(function ($d) use ($tour) {
                $start = new Carbon($d->start);
                return $start->subDays($tour->expiration) > now() ? $d : null;
            })->first();
        }

        return [
            'id' => $date->id,
            'start' => $date->start,
            'end' => $date->end,
        ];
    }

    public function getHotel(Hotel $hotel)
    {
        return [
            'id' => $hotel->id,
            'name' => $hotel->name,
            'address' => $hotel->address,
            'stars' => $hotel->stars,
            'photo' => $hotel->gallery->map(fn($item) => Storage::disk('public')->url($item))
        ];
    }
}
