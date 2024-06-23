<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TourReservationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $tour = $this->tour;
        $support = $tour->support;
        $hotel = $this->hotel;
        return [
            'total_price' => $this->total_price,
            'passengers_count' => $this->passengers_count,
            'passengers' => $this->passengers,
            'status' => $this->status,
            'tour' => [
                'id' => $tour->id,
                'title' => $tour->title,
                'support' => [
                    'name' => $support->name,
                    'phone' => $support->phone,
                ],
                'trip_type' => $tour->trip_type,
                'tour_styles' => $tour->tour_styles,
                'evening_support' => $tour->evening_support,
                'midnight_support' => $tour->midnight_support,
                'origin' => $tour->origin,
                'destination' => $tour->destination,
                'staying_nights' => $tour->staying_nights,
            ],
            'agency' => [
                'id' => $tour->agency->id,
                'name' => $tour->agency->name,
            ],
            'date' => [
                'id' => $this->date->id,
                'start' => $this->date->start,
                'end' => $this->date->end,
            ],
            'hotel' => [
                'id' => $hotel->id,
                'name' => $hotel->name,
                'address' => $hotel->address,
                'stars' => $hotel->stars,
                'photo' => $hotel->firstPhotoUrl(),
            ],
        ];
    }
}
