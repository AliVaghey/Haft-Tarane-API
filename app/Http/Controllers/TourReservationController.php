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

    public function getReservations(Request $request)
    {
        $reservations = TourReservation::where('user_id', $request->user()->id)->get();
        return TourReservationResource::collection($reservations->orderByDesc('created_at'))->paginate($request->query('per_page', 10));
    }

    public function getReservation(TourReservation $reservation)
    {
        return new TourReservationResource($reservation);
    }

    private function totalPrice(Tour $tour, Date $date, Costs $cost, $passengers)
    {
        $passengers = json_decode($passengers, true);
        $total_price = 0;
        foreach ($passengers as $room) {
            foreach ($room as $passenger) {
                switch (strtolower($passenger['type'])) {
                    case 'adl':
                        $total_price += $room['room_type'] == 'one_bed' ? $cost->one_bed : $cost->two_bed;
                        break;

                    case 'cld_2':
                        $total_price += $cost->cld_2;
                        break;

                    case 'cld_6':
                        $total_price += $cost->cld_6;
                        break;

                    case 'baby':
                        $total_price += $cost->baby;
                        break;
                }
            }
            $total_price += $cost->plus_one * $room['plus_one'];
        }
        if ($tour->isSysTrans()) {
            foreach($passengers as $room) {
                foreach ($room as $passenger) {
                    switch (strtolower($passenger['type'])) {
                        case 'adl':
                            $total_price += $this->getTransPrice($tour, $date, $passenger['type']);
                            break;

                        case 'cld_2':
                        case 'cld_6':
                            $total_price += $this->getTransPrice($tour, $date, 'cld');
                            break;

                        case 'baby':
                            $total_price += $this->getTransPrice($tour, $date, $passenger['type']);
                            break;
                    }
                }
            }
        }
        return $total_price;
    }

    private function getTransPrice(Tour $tour, Date $date, string $passenger_type)
    {
        $total_price = 0;
        $transportations = SysTransport::where('tour_id', $tour->id)->where('date_id', $date->id)->get();
        foreach ($transportations as $trans) {
            $ticket = $trans->getTicket();
            switch ($passenger_type) {
                case 'adl':
                    $total_price += $ticket->price_final;
                    break;

                case 'cld':
                    $total_price += $ticket->price_final_chd;
                    break;

                case 'baby':
                    $total_price += $ticket->price_final_inf;
                    break;
            }
        }
        return $total_price;
    }
}
/*
{
    {
        beds =
    }
}



 */
