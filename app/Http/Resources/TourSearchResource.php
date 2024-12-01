<?php

namespace App\Http\Resources;

use App\Models\Hotel;
use App\Models\PriceChange;
use App\Models\SysTransport;
use App\Models\Tour;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TourSearchResource extends JsonResource
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
            'id' => $tour->id,
            'agency_name' => $tour->agency->name,
            'title' => $tour->title,
            'trip_type' => $tour->trip_type,
            'capacity' => $tour->capacity,
            'origin' => $tour->origin,
            'destination' => $tour->destination,
            'min_cost' => $this->minCost($tour, $date),
            'status' => $tour->status,
            'dates' => $date,
            'costs' => $this->filterCosts(),
            'transportation' => null,
            'labels' => $tour->labels
        ];
    }

    public function minCost(Tour $tour, $date)
    {
        if ($tour->isSysTrans()) {
            $total_price = $this->two_bed;
            foreach (SysTransport::where('date_id', $date[0]['id'])->get() as $transport) {
                $total_price += ($transport->flight->price_final / 10);
            }
        } else {
            $total_price = $this->two_bed;
        }
        $price_change = PriceChange::where('date_id', $date[0]['id'])->where('cost_id', $this->id)->get();
        if ($price_change->isNotEmpty()) {
            $price_change = $price_change->first();
            $total_price += $price_change->toCurrency()->price_change;
        }
        return $total_price;
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
                if ($input == $start && !$d->expired) {
                    $date = $d;
                    break;
                }
            }
            if (!$date) {
                return [
                    'id' => 0,
                    'start' => 0,
                    'end' => 0,
                ];
            }
        } else {
            $date = $tour->dates->firstWhere('expired', false);
        }

        return [
            [
                'id' => $date->id,
                'start' => $date->start,
                'end' => $date->end,
            ]
        ];
    }

    /**
     * It returns a collection of filtered costs.
     *
     */
    private function filterCosts()
    {
        $cost = collect();
        $c = [
            'id' => $this->id,
            'room_type' => $this->room_type,
            'one_bed' => $this->one_bed,
            'two_bed' => $this->two_bed,
            'plus_one' => $this->plus_one,
            'cld_6' => $this->cld_6,
            'cld_2' => $this->cld_2,
            'baby' => $this->baby,
        ];
        if ($hotel = Hotel::find($this->hotel_id)) {
            $c['hotel'] = [
                'id' => $hotel->id,
                'name' => $hotel->name,
                'address' => $hotel->address,
                'photo' => $hotel->firstPhotoUrl()
            ];
        } else {
            $c['hotel'] = null;
        }
        $cost->push($c);
        return $cost;
    }
}
