<?php

namespace App\Http\Resources;

use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

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
            'id' => $this->id,
            'total_price' => $this->total_price,
            'passengers_count' => $this->passengers_count,
            'passengers' => $this->passengers,
            'status' => ($this->status == 'checkedout' && $request->user()->isUser()) ? 'paid' : $this->status,
            'tour' => [
                'id' => $tour->id,
                'title' => $tour->title,
                'support' => [
                    'name' => $support->name,
                    'phone' => $support->phone,
                ],
                'transportation_type' => $tour->transportation_type,
                'trip_type' => $tour->trip_type,
                'tour_styles' => $tour->tour_styles,
                'evening_support' => $tour->evening_support,
                'midnight_support' => $tour->midnight_support,
                'origin' => $tour->origin,
                'destination' => $tour->destination,
                'staying_nights' => $tour->staying_nights,
                'certificate' => $tour->certificate,
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
            'user' => $this->user,
            'transportation' => $tour->isSysTrans() ? $this->getSysTrans($tour, $this->date) : $tour->transportations->sortBy("sort"),
            'files' => $this->files->files->map(function ($path) {
                return Storage::disk('public')->url($path);
            }),
        ];
    }

    public function getSysTrans(Tour $tour, $date)
    {
        $result = [];
        foreach ($tour->sysTransport->where('date_id', $date->id) as $transport) {
            $result [] = [
                'transportation_id' => $transport->id,
                'flight' => $transport->flight
            ];
        }
        return $result;
    }
}
