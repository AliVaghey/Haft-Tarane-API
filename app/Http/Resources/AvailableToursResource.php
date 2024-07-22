<?php

namespace App\Http\Resources;

use App\Models\Costs;
use App\Models\Hotel;
use App\Models\SysTransport;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AvailableToursResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $tour = $this->tour;
        $date = $this->date;
        $cost = $this->cost;
        return [
            'id' => $tour->id,
            'agency_name' => $tour->agency->name,
            'title' => $tour->title,
            'trip_type' => $tour->trip_type,
            'capacity' => $tour->capacity,
            'origin' => $tour->origin,
            'destination' => $tour->destination,
            'min_cost' => (int)$this->min_cost,
            'status' => $tour->status,
            'dates' => [[
                'id' => $date->id,
                'start' => $date->start,
                'end' => $date->end
            ]],
            'costs' => $this->filterCosts($cost),
            'transportation' => $this->tour->isSysTrans() ? $this->getSysTrans() : $this->tour->transportations->sortBy("sort"),
            'tour' => $tour,
        ];
    }

    private function getSysTrans()
    {
        return SysTransport::where('date_id', $this->date->id)->get()->map(function ($item) {
            return ['transportation_id' => $item->id, 'flight' => $item->flight];
        });
    }

    private function filterCosts(Costs $cost_model)
    {
        $cost = collect();
        $c = [
            'id' => $cost_model->id,
            'room_type' => $cost_model->room_type,
            'one_bed' => $cost_model->one_bed,
            'two_bed' => $cost_model->two_bed,
            'plus_one' => $cost_model->plus_one,
            'cld_6' => $cost_model->cld_6,
            'cld_2' => $cost_model->cld_2,
            'baby' => $cost_model->baby,
        ];
        if ($hotel = Hotel::find($cost_model->hotel_id)) {
            $c['hotel'] = [
                'id' => $hotel->id,
                'name' => $hotel->name,
                'address' => $hotel->address,
                'stars' => $hotel->stars,
                'photo' => $hotel->firstPhotoUrl()
            ];
        } else {
            $c['hotel'] = null;
        }
        $cost->push($c);
        return $cost;
    }
}
