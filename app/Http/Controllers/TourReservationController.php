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
            'rooms' => ['required', 'json'],
        ]);

        $reservation = TourReservation::create([
            'user_id' => $request->user()->id,
            'tour_id' => $tour->id,
            'date_id' => $date->id,
            'cost_id' => $cost->id,
            'hotel_id' => $cost->hotel_id,
            'agency_id' => $tour->agency_id,
            'total_price' => $this->totalPrice($tour, $date, $cost, $request->get('rooms')),
            'passengers' => collect(json_decode($request->get('rooms'), true)),
            'passengers_count' => $this->countPassengers(json_decode($request->get('rooms'), true)),
        ]);

        return response($reservation, 201);
    }

    public function getReservations(Request $request)
    {
        $reservations = TourReservation::where('user_id', $request->user()->id);
        return TourReservationResource::collection($reservations->orderBy('created_at', 'desc')->paginate($request->query('per_page', 10)));
    }

    public function getReservation(Request $request, TourReservation $reservation)
    {
        if ($request->user()->id != $reservation->user_id) {
            return response(['message' => __('exceptions.not-own-res')], 403);
        }
        return new TourReservationResource($reservation);
    }

    private function totalPrice(Tour $tour, Date $date, Costs $cost, $passengers)
    {
        $passengers = json_decode($passengers, true);
        $total_price = 0;
        foreach ($passengers as $room) {
            foreach ($room['passengers'] as $passenger) {
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
                foreach ($room['passengers'] as $passenger) {
                    switch (strtolower($passenger['type'])) {
                        case 'baby':
                        case 'adl':
                            $total_price += $this->getTransPrice($tour, $date, $passenger['type']);
                            break;

                        case 'cld_2':
                        case 'cld_6':
                            $total_price += $this->getTransPrice($tour, $date, 'cld');
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
                    $total_price += ($ticket->price_final / 10);
                    break;

                case 'cld':
                    $total_price += ($ticket->price_final_chd / 10);
                    break;

                case 'baby':
                    $total_price += ($ticket->price_final_inf / 10);
                    break;
            }
        }
        return $total_price;
    }

    private function countPassengers(mixed $passengers)
    {
        $count = 0;
        foreach ($passengers as $room) {
            foreach ($room['passengers'] as $passenger) {
                $count++;
            }
        }
        return $count;
    }
}
