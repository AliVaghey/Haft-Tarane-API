<?php

namespace App\Http\Controllers;

use App\Enums\UserAccessType;
use App\Http\Resources\TourReservationResource;
use App\Models\Costs;
use App\Models\Date;
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
            return response(['message' => "تنها کاربران قادر به رزرو تور می باشند."]);
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
            'total_price' => $this->totalPrice($tour, $date, $cost, $request->get('rooms')),
            'passengers' => collect(json_decode($request->get('rooms'), true)),
            'passengers_count' => $count,
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
        $agency_message = "آژانس محترم {$tour->agency->name}";
        $agency_message .= "\nخواهشمند است نسبت به تامین کد تور {$tour->id}";
        $agency_message .= "به تاریخ {$start}";
        $agency_message .= "به مدت {$tour->staying_nights}";
        $agency_message .= " شب اقدام فرمایید.";

        $user_message = "مسافر محترم {$user->username}";
        $user_message .= "\nدرخواست شما ثبت شد و در حال پیگیری می باشد. لطفا با پشتیبان تور تماس بگیرید.";
        $user_message .= "نام و شماره پشتیبان :\n";
        $user_message .= $tour->support->name . " - " . $tour->support->phone;

        $sms = sms();
        $sms->send($tour->agency->user->phone, $agency_message . "\nلغو 11");
        $sms->send($user->phone, $user_message . "\nلغو 11");
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
