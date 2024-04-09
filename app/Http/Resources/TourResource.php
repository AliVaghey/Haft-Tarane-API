<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TourResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $certificate = $this->certificate;
        return [
            'id' => $this->id,
            'agency_name' => $this->agency->name,
            'title' => $this->title,
            'trip_type' => $this->trip_type,
            'capacity' => $this->capacity,
            'expiration' => $this->expiration,
            'selling_type' => $this->selling_type,
            'tour_styles' => $this->tour_styles,
            'evening_support' => $this->evening_support,
            'midnight_support' => $this->midnight_support,
            'transportation_id' => $this->transportation_id,
            'origin' => $this->origin,
            'destination' => $this->destination,
            'staying_nights' => $this->staying_nights,
            'transportation_type' => $this->transportation_type,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'certificate' => $this->when($certificate, [
                'free_services' => $certificate ? $certificate->free_services : null,
                'certificates' => $certificate ? $certificate->certificates : null,
                'descriptions' => $certificate ? $certificate->descriptions : null,
                'cancel_rules' => $certificate ? $certificate->cancel_rules : null,
            ]),
        ];
    }
}
