<?php

namespace App\Http\Resources;

use App\Models\Costs;
use App\Models\Hotel;
use App\Models\PriceChange;
use App\Models\SysTransport;
use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SimilarDateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if ($cost = Costs::find($request->query('cost_id'))) {
            $tour = $this->tour;
            return [
                'id' => $tour->id,
                'agency_name' => $tour->agency->name,
                'title' => $tour->title,
                'trip_type' => $tour->trip_type,
                'capacity' => $tour->capacity,
                'origin' => $tour->origin,
                'destination' => $tour->destination,
                'min_cost' => $this->minCost($tour, $cost),
                'status' => $tour->status,
                'dates' => [
                    [
                        'id' => $this->id,
                        'start' => $this->start,
                        'end' => $this->end
                    ]
                ],
                'costs' => $this->makeCost($cost),
                'transportation' => null,
            ];
        } else {
            return [];
        }
    }

    public function minCost(Tour $tour, $cost)
    {
        $total_price = $cost->two_bed;
        if ($tour->isSysTrans()) {
            foreach (SysTransport::where('date_id', $this->id)->get() as $transport) {
                $total_price += ($transport->flight->price_final / 10);
            }
        } else {
            foreach($tour->transportations as $trans) {
                $total_price += $trans->price;
            }
        }
        $price_change = PriceChange::where('date_id', $this->id)->where('cost_id', $cost->id)->get();
        if ($price_change->isNotEmpty()) {
            $price_change = $price_change->first();
            $total_price += $price_change->toCurrency()->price_change;
        }
        return $total_price;
    }

    private function makeCost($cost)
    {
        $c = [
            'id' => $cost->id,
            'room_type' => $cost->room_type,
            'one_bed' => $cost->one_bed,
            'two_bed' => $cost->two_bed,
            'plus_one' => $cost->plus_one,
            'cld_6' => $cost->cld_6,
            'cld_2' => $cost->cld_2,
            'baby' => $cost->baby,
        ];
        if ($hotel = Hotel::find($cost->hotel_id)) {
            $c['hotel'] = [
                'id' => $hotel->id,
                'name' => $hotel->name,
                'address' => $hotel->address,
                'photo' => $hotel->firstPhotoUrl()
            ];
        } else {
            $c['hotel'] = null;
        }
        return collect($c);
    }
}
