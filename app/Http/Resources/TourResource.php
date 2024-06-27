<?php

namespace App\Http\Resources;

use App\Models\Hotel;
use App\Models\PriceChange;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

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
            'support' => $this->support ? [
                'id' => $this->support->id,
                'name' => $this->support->name,
            ] : [],
            'evening_support' => $this->evening_support,
            'midnight_support' => $this->midnight_support,
            'transportation_id' => $this->transportation_id,
            'origin' => $this->origin,
            'destination' => $this->destination,
            'staying_nights' => $this->staying_nights,
            'transportation_type' => $this->transportation_type,
            'status' => $this->status,
            'costs' => $this->filterCosts(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'transportations' => $this->isSysTrans() ? $this->getSysTrans() : $this->transportations->sortBy("sort"),
            'certificate' => [
                'free_services' => $certificate ? $certificate->free_services : null,
                'certificates' => $certificate ? $certificate->certificates : null,
                'descriptions' => $certificate ? $certificate->descriptions : null,
                'tab_descriptions' => $certificate ? $certificate->tab_descriptions : null,
                'cancel_rules' => $certificate ? $certificate->cancel_rules : null,
            ],
            'dates' => $this->dates->map(function ($date) {
                return [
                    'id' => $date->id,
                    'start' => $date->start,
                    'end' => $date->end,
                    'expired' => $date->expired,
                    'price_change' => $date->price_change
                ];
            }),
            'profit_rate' => $this->profit_rate,
            'price_changes' => $this->getPriceChanges($this->costs),
        ];
    }

    public function getPriceChanges($costs)
    {
        $priceChanges = collect();
        foreach ($costs as $cost) {
            $priceChanges->push(PriceChange::where('cost_id', $cost->id)->get());
        }
        return PriceChangeResource::collection($priceChanges->flatten(1));
    }

//    /**
//     * It returns a filtered collection of hotels. (DEPRECATED)
//     *
//     * @return Collection
//     */
//    private function filterHotels()
//    {
//        $hotels = collect();
//        foreach ($this->hotels as $hotel) {
//            if ($h = Hotel::find($hotel)) {
//                $hotels->push([
//                    'id' => $h->id,
//                    'name' => $h->name,
//                ]);
//            }
//        }
//        return $hotels;
//    }

    public function getSysTrans()
    {
        return $this->sysTransport->map(function ($transport) {
            return ['transportation_id' => $transport->id, 'flight' => $transport->flight];
        });
    }

    /**
     * It returns a collection of filtered costs.
     *
     * @return Collection
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
                    'photo' => $hotel->gallery->map(function ($item) {
                        return Storage::disk('public')->url($item);
                    })
                ];
            } else {
                $c['hotel'] = null;
            }
            $costs->push($c);
        }
        return $costs;
    }
}
