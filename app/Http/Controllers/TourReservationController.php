<?php

namespace App\Http\Controllers;

use App\Enums\UserAccessType;
use App\Http\Resources\TourReservationResource;
use App\Models\Costs;
use App\Models\Date;
use App\Models\PriceChange;
use App\Models\ReservationFile;
use App\Models\SysTransport;
use App\Models\Tour;
use App\Models\TourReservation;
use App\Models\User;
use Illuminate\Http\Request;
use IntlDateFormatter;

class TourReservationController extends Controller
{
    public function reserve(Request $request, Tour $tour, Date $date, Costs $cost)
    {
        $request->validate(['rooms' => ['required', 'json']]);
        $count = $this->countPassengers(json_decode($request->get('rooms'), true));
        if ($request->user()->access_type != UserAccessType::User) {
            return response(['message' => "تنها کاربران قادر به رزرو تور می باشند."], 403);
        }
        if ($count > $tour->capacity) {
            return response(['message' => "ظرفیت این تور تنها {$tour->capacity} نفر می باشد."], 403);
        }

        $user = $request->user();
        $reservation = TourReservation::create([
            'user_id' => $user->id,
            'tour_id' => $tour->id,
            'date_id' => $date->id,
            'cost_id' => $cost->id,
            'hotel_id' => $cost->hotel_id,
            'agency_id' => $tour->agency_id,
            'total_price' => $this->totalPrice($tour, $date, $cost, $request->get('rooms'), $count),
            'passengers' => collect(json_decode($request->get('rooms'), true)),
            'passengers_count' => $count,
        ]);

        ReservationFile::create([
            'reservation_id' => $reservation->id,
            'files' => collect()
        ]);

        $this->sendMessages($user, $tour, $date, $cost, $reservation);

        return response(new TourReservationResource($reservation), 201);
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

    private function totalPrice(Tour $tour, Date $date, Costs $cost, $passengers, int $count)
    {
        $passengers = json_decode($passengers, true);
        $total_price = 0;
        $price_change = PriceChange::where('date_id', $date->id)->where('cost_id', $cost->id)->first();
        foreach ($passengers as $room) {
            foreach ($room['passengers'] as $passenger) {
                switch (strtolower($passenger['type'])) {
                    case 'adl':
                        $total_price += $room['room_type'] == 'one_bed' ? $cost->one_bed : $cost->two_bed;
                        if ($price_change) {
                            $total_price += $room['room_type'] == 'one_bed' ? $price_change->one_bed : $price_change->two_bed;
                        }
                        break;

                    case 'cld_2':
                        $total_price += $cost->cld_2;
                        if ($price_change) {
                            $total_price += $price_change->cld_2;
                        }
                        break;

                    case 'cld_6':
                        $total_price += $cost->cld_6;
                        if ($price_change) {
                            $total_price += $price_change->cld_6;
                        }
                        break;

                    case 'baby':
                        $total_price += $cost->baby;
                        if ($price_change) {
                            $total_price += $price_change->baby;
                        }
                        break;
                }
            }
            $total_price += $cost->plus_one * $room['plus_one'];
            if ($price_change) {
                $total_price += $price_change->plus_one * $room['plus_one'];
            }
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
        } else {
            $count = $this->countPassengers($passengers);
            foreach ($tour->transportations as $transportation) {
                $total_price += $transportation->price * $count;
            }
        }
//        $price_change = PriceChange::where('date_id', $date->id)->where('cost_id', $cost->id)->get();
//        if ($price_change->isNotEmpty()) {
//            $price_change = $price_change->first();
//            $total_price += ($count * $price_change->price_change);
//        }
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

    public function getAgencyReservations(Request $request)
    {
        $results = TourReservation::where('agency_id', $request->user()->agencyInfo->id);
        if ($request->query('pending')) {
            $results = $results->where('status', 'pending');
        } elseif ($request->query('all')) {
            $results = $results->where('status', 'paid');
        } elseif ($request->query('checkedout')) {
            $results = $results->where('status', 'checkedout');
        }
        return TourReservationResource::collection(
            $results
                ->orderBy('created_at', 'desc')
                ->paginate($request->query('per_page', 10))
        );
    }

    public function getAgencyReservation(Request $request, TourReservation $reservation)
    {
        if ($request->user()->agencyInfo->id != $reservation->agency_id) {
            return response(['message' => __('exceptions.not-own-res')], 403);
        }
        return new TourReservationResource($reservation);
    }

    public function changeReservationStatus(Request $request, TourReservation $reservation)
    {
        $request->validate([
            'status' => ['required', 'string']
        ]);

        if ($request->status == 'paid') {
            $reservation->update(['status' => 'paid']);
        } elseif ($request->status == 'pending') {
            $reservation->update(['status' => 'pending']);
        } else {
            return response(['message' => __('exceptions.not-allowed')], 422);
        }

        return response($reservation, 200);
    }

    private function sendMessages(User $user, Tour $tour, Date $date, Costs $cost, $reservation)
    {
        $start = $this->getShamsi($date->start);
        $agency_params = [
            'AGENCY' => $tour->agency->name,
            'TOUR_ID' => $tour->id,
            'DATE' => $start,
            'NIGHTS' => $tour->staying_nights
        ];
        $user_params = [
            'NAME' => $user->username,
            'SUPPORT' => $tour->support->name,
            'PHONE' => $tour->support->phone
        ];
        $super_admin_params = [
            'USER' => $user->username,
            'TOUR' => $tour->id,
            'AGENCY' => $tour->agency->name,
        ];
        $super_admins = User::where('access_type', 'superadmin')->get();

        $sms = sms();
        foreach ($super_admins as $sa) {
            $sms->send($super_admin_params, 797606, $sa->phone);
        }
        $sms->send($agency_params, 528983, $tour->agency->user->phone);
        $sms->send($user_params, 936437, $user->phone);
    }

    public function getShamsi($date)
    {
        $formatter = new IntlDateFormatter(
            "fa_IR@calendar=persian",
            IntlDateFormatter::FULL,
            IntlDateFormatter::FULL,
            'Asia/Tehran',
            IntlDateFormatter::TRADITIONAL,
            "yyyy-MM-dd");
        $start_date = new \DateTime($date);
        return $formatter->format($start_date);
    }
}
