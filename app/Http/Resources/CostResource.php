<?php

namespace App\Http\Resources;

use App\Models\Hotel;
use App\Models\PriceChange;
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
            'cost' => $this->costDetails($date),
            'date' => $date,
            'certificate' => $tour->certificate,
        ];
    }

    private function costDetails($date)
    {
        $price_change = PriceChange::where('cost_id', $this->id)->where('date_id', $date['id'])->first();
        if ($price_change) {
            return [
                'id' => $this->id,
                'tour_id' => $this->tour_id,
                'hotel_id' => $this->hotel_id,
                'room_type' => $this->room_type,
                'one_bed' => $this->one_bed + $price_change->one_bed,
                'two_bed' => $this->two_bed + $price_change->two_bed,
                'plus_one' => $this->plus_one + $price_change->plus_one,
                'cld_6' => $this->cld_6 + $price_change->cld_6,
                'cld_2' => $this->cld_2 + $price_change->cld_2,
                'baby' => $this->baby + $price_change->baby,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
                'tour' => $this->tour,
                'hotel' => $this->hotel
            ];
        } else {
            return [
                'id' => $this->id,
                'tour_id' => $this->tour_id,
                'hotel_id' => $this->hotel_id,
                'room_type' => $this->room_type,
                'one_bed' => $this->one_bed,
                'two_bed' => $this->two_bed,
                'plus_one' => $this->plus_one,
                'cld_6' => $this->cld_6,
                'cld_2' => $this->cld_2,
                'baby' => $this->baby,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
                'tour' => $this->tour,
                'hotel' => $this->hotel
            ];
        }
    }

    public function minCost(Tour $tour, $date)
    {
        $total_price = 0;
        if ($tour->isSysTrans()) {
            $total_price = $this->two_bed;
            foreach (SysTransport::where('date_id', $date['id'])->get() as $transport) {
                $total_price += ($transport->flight->price_final / 10);
            }
        } else {
            $total_price = $this->two_bed;
        }
        $price_change = PriceChange::where('date_id', $date['id'])->where('cost_id', $this->id)->first();
        if ($price_change) {
            $total_price += $price_change->two_bed;
        }
        return $total_price;
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
            'expired' => $date->expired,
            'price_change' => $date->price_change,
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
