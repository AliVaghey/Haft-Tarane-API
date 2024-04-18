<?php

namespace App\Http\Resources;

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
        $min_cost = $this->costs ? $this->costs->min('one_bed') : '';
        return [
            'id' => $this->id,
            'agency_name' => $this->agency->name,
            'title' => $this->title,
            'trip_type' => $this->trip_type,
            'capacity' => $this->capacity,
            'origin' => $this->origin,
            'destination' => $this->destination,
            'min_cost' => $min_cost,
        ];
    }
}
