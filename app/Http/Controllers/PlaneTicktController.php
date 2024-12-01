<?php

namespace App\Http\Controllers;

use App\Http\Resources\PlaneTicketResource;
use App\Models\FlightInfo;
use App\Models\PlaneTickt;
use App\Models\User;
use Illuminate\Http\Request;

class PlaneTicktController extends Controller
{
    public function getAll(Request $request)
    {
        return PlaneTicketResource::collection($request->user()->planeTickets()->paginate());
    }

    public function read(PlaneTickt $ticket)
    {
        return new PlaneTicketResource($ticket);
    }

    public function getCaptcha(Request $request)
    {
        $request->validate([
            'uniqueID' => ['required', 'string']
        ]);

        try {
            return air_service()->getCaptcha($request->get('uniqueID'));
        } catch (\Exception $exception) {
            return response(['message' => $exception->getMessage()], 400);
        }
    }

    public function reserveTicket(Request $request)
    {
        $request->validate([
            'uniqueID' => 'required',
            'requestID' => 'required',
            'mobile' => 'required',
            'captchaCode' => 'required',
            'email' => 'nullable',
            'passengers' => 'required|json',
            'flight_info' => 'required|json',
        ]);

        $results = null;
        $flight_info = json_decode($request->get('passengers', true));
        try {
            $results = air_service()->reserveTicket(
                $request->get('uniqueID'),
                $request->get('requestID'),
                $request->get('captchaCode'),
                $request->get('mobile'),
                $request->get('email'),
                $flight_info,
            );
        } catch (\Exception $exception) {
            return response(['message' => $exception->getMessage()], 400);
        }

        $ticket = $this->createTicketAndFlightInfo(
            $request->user(),
            $results,
            json_decode($request->get('passengers'), true),
            $flight_info
        );

        return response([
            'paymentUrl' => route('payment.planeTicket', ['ticket' => $ticket->id]),
            'reservation_results' => $results
        ]);
    }

    private function createTicketAndFlightInfo(User $user, $result, $passengers, $flight_info): \Illuminate\Database\Eloquent\Model
    {
        $flight = FlightInfo::create($flight_info);

        $ticket = $user->planeTickets()->create([
            'flight_info_id' => $flight->id,
            'total_price' => (int)($result['totalPrice'] / 10),
            'passengers' => $passengers,
            'reservation_results' => $result,
            'voucher' => $result['voucher']
        ]);

        return $ticket;
    }
}
