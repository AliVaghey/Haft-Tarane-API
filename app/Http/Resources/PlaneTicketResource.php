<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlaneTicketResource extends JsonResource
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
            'transaction' => $this->transaction,
            'flightInfo' => $this->flightInfo,
            'total_price' => $this->total_price,
            'passengers' => $this->passengers,
            'reservation_results' => $this->reservation_results,
            'buy_ticket_results' => $this->buy_ticket_results,
            'descriptions' => $this->descriptions,
            'status' => $this->status,
            'voucher' => $this->voucher,
        ];
    }
}
