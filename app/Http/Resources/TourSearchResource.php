<?php

namespace App\Http\Resources;

use App\Models\Hotel;
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
        return [
            'id' => $tour->id,
            'agency_name' => $tour->agency->name,
            'title' => $tour->title,
            'trip_type' => $tour->trip_type,
            'capacity' => $tour->capacity,
            'origin' => $tour->origin,
            'destination' => $tour->destination,
            'min_cost' => $this->two_bed,
            'status' => $tour->status,
            'dates' => $tour->dates->map(fn($date) => [
                'id' => $date->id,
                'start' => $date->start,
                'end' => $date->end,
            ]),
            'costs' => $this->filterCosts(),
            'transportation' => $tour->transportation,
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
