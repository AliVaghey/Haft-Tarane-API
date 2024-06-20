<?php

namespace App\Http\Resources;

use App\Models\Tour;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'cost' => parent::toArray($request),
            'date' => $this->findDate($tour, $request->query('start')),
        ];
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
}
