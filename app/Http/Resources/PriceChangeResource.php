<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PriceChangeResource extends JsonResource
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
            'hotel_name' => $this->cost->hotel->name,
            'start' => $this->date->start,
            'end' => $this->date->end,
            'price_change' => $this->price_change,
            'one_bed' => $this->one_bed,
            'two_bed' => $this->two_bed ,
            'plus_one' => $this->plus_one ,
            'cld_6' => $this->cld_6 ,
            'cld_2' => $this->cld_2 ,
            'baby' => $this->baby
        ];
    }
}
