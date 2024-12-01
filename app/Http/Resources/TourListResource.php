<?php

namespace App\Http\Resources;

use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class TourListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'agency_name' => $this->agency->name,
            'title' => $this->title,
            'staying_nights' => $this->staying_nights,
            'trip_type' => $this->trip_type,
            'capacity' => $this->capacity,
            'origin' => $this->origin,
            'destination' => $this->destination,
            'min_cost' => $this->costs->isNotEmpty() ? $this->minCost() : "No Cost",
            'status' => $this->status,
            'dates' => $this->dates->map(fn ($date) => [
                'id' => $date->id,
                'start' => $date->start,
                'end' => $date->end,
            ]),
            'labels' => $this->labels,
            'costs' => $this->filterCosts(),
            'transportation' => $this->transportations,
        ];
    }

    public function minCost()
    {
        $min = $this->costs->min('two_bed');
        foreach ($this->transportations as $trans) {
            $min += $trans->price;
        }

        return $min;
    }

    /**
     * It returns a collection of filtered costs.
     *
     */
    private function filterCosts()
    {
        $costs = collect();
        foreach ($this->costs as $cost) {
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
            $costs->push($c);
        }
        return $costs;
    }
}
