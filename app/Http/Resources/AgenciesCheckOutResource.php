<?php

namespace App\Http\Resources;

use App\Enums\ReservationStatus;
use App\Models\TourReservation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AgenciesCheckOutResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $dept = 0;
        $profit = 0;
        $total_sales = 0;
        $sales = TourReservation::where('agency_id', $this->id)->where('status', 'paid')->get();
        foreach ($sales as $sale) {
            $dept += $sale->total_price - ($sale->tour->profit_rate * $sale->total_price / 100);
            $profit += $sale->total_price * ($sale->tour->profit_rate / 100);
            $total_sales += $sale->total_price;
        }
        return [
            parent::toArray($request),
            'dept' => $dept,
            'profit' => $profit,
            'total_sales' => $total_sales,
        ];
    }
}
