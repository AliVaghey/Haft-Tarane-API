<?php

namespace App\Http\Controllers;

use App\Http\Resources\TourReservationResource;
use App\Models\Costs;
use App\Models\Date;
use App\Models\SysTransport;
use App\Models\Tour;
use App\Models\TourReservation;
use Illuminate\Http\Request;

class TourReservationController extends Controller
{
    public function reserve(Request $request, Tour $tour, Date $date, Costs $cost)
    {
        $request->validate([
            'passengers' => ['required', 'json'],
        ]);

        $reservation = TourReservation::create([
            'user_id' => $request->user()->id,
            'tour_id' => $tour->id,
            'date_id' => $date->id,
            'cost_id' => $cost->id,
            'hotel_id' => $cost->hotel_id,
            'agency_id' => $tour->agency_id,
            'total_price' => $this->totalPrice($tour, $date, $cost, $request->get('passengers')),
        ]);
    }

    public function reservations(Request $request)
    {
        $reservations = TourReservation::where('user_id', $request->user()->id)->get();
        return TourReservationResource::collection($reservations->orderByDesc('created_at'))->paginate($request->query('per_page', 10));
    }

    public function reservation(TourReservation $reservation)
    {
        return new TourReservationResource($reservation);
    }

    private function totalPrice(Tour $tour, Date $date, Costs $cost, $passengers)
    {
        $passengers = json_decode($passengers, true);
        $total_price = 0;
        foreach ($passengers as $passenger) {
            switch (strtolower($passenger['type'])) {
                case 'adl':
                    $total_price += $this->getAdultPrice($tour, $date, $cost, $passenger);
                    break;
                case 'chl':
                    $total_price += $this->getChildPrice($tour, $date, $cost, $passenger);
                    break;
                case 'inf':
                    $total_price += $this->getInfentPrice($tour, $date, $cost, $passenger);
            }
        }


        return 0;
    }

    private function getAdultPrice(Tour $tour, Date $date, Costs $cost, mixed $passenger)
    {
        $price = 0;
        if ($tour->isSysTrans()) {
            // calculate going ticket
            $trans = SysTransport::where('date_id', $date->id)->where('returning', 0)->get()->first();
            $price = $trans->flight->price_final;
        } else {

        }
    }

    private function getChildPrice(Tour $tour, Date $date, Costs $cost, mixed $passenger)
    {
    }

    private function getInfentPrice(Tour $tour, Date $date, Costs $cost, mixed $passenger)
    {
    }
}
