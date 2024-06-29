<?php

namespace App\Http\Resources;

use App\Models\Date;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SpecialTourResource extends JsonResource
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
            'tour' => [
                'id' => $this->tour->id,
                'title' => $this->tour->title,
                'agency_name' => $this->tour->agency->name,
                'origin' => $this->tour->origin,
                'destination' => $this->tour->destination,
                'trip_type' => $this->tour->trip_type,
            ],
            'importance' => $this->importance,
            'advertisement' => $this->advertisement,
            'photo' => Storage::disk('public')->url($this->photo),
            'dates' => $this->dates->map(function ($date) {
                return Date::find($date);
            })
        ];
    }
}
