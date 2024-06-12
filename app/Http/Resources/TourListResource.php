<?php

namespace App\Http\Resources;

use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'costs' => $this->filterCosts(),
            'transportation' => $this->transportation,
        ];
    }

    public function minCost()
    {
        $min = $this->costs->min('one_bed');
        foreach ($this->transportation as $trans) {
            $min += $trans->price;
        }

        return $min;
    }

    /**
     * It returns a collection of filtered costs.
     *
     * @return Collection
     */
    private function filterCosts()
    {
        $costs = collect();
        if ($this->costs) {
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
        }
        return $costs;
    }
}
