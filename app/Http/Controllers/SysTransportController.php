<?php

namespace App\Http\Controllers;

use App\Models\Date;
use App\Models\FlightInfo;
use App\Models\SysTransport;
use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SysTransportController extends Controller
{
    public function addTransport(Request $request, Tour $tour)
    {
        $request->validate([
            'returning' => ['required', 'boolean'],
            'start_date' => ['nullable', 'date'],
            'price_final' => ['numeric'],
            'price_final_chd' => ['numeric'],
            'price_final_inf' => ['numeric'],
            'price_final_fare' => ['numeric'],
            'price_final_chd_fare' => ['numeric'],
            'price_final_inf_fare' => ['numeric'],
            'date_flight' => ['date'],
            'capacity' => ['numeric', 'max:255'],
        ]);

        if ($request->get('capacity') == 0) {
            return response(['message' => "ظرفیت پرواز انتخاب شده صفر می باشد."], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $flight = FlightInfo::create($request->only([
            'uniqueID',
            'type',
            'price_final',
            'price_final_chd',
            'price_final_inf',
            'price_final_fare',
            'price_final_chd_fare',
            'price_final_inf_fare',
            'capacity',
            'from',
            'to',
            'number_flight',
            'type_flight',
            'carrier',
            'date_flight',
            'time_flight',
            'airline',
            'IATA_code',
            'cabinclass',
            'SellingType',
            'weelchairsupport',
            'price_Markup',
            'Share_Sale',
            'has_stop',
            'alarm_msg',
        ]));

        if ((bool)$request->get('returning')) {
            $date_model = Date::where('tour_id', $tour->id)->where('start', $request->get('start_date'))->get()->first();
            if (!$date_model) {
                return response(['message' => "تاریخ رفت وجود ندارد."], 403);
            }
            $date_model->update(['end' => $request->get('date_flight')]);
        } else {
            $date_model = Date::create([
                'tour_id' => $tour->id,
                'start' => $request->get('date_flight'),
            ]);
        }

        $transportation = SysTransport::create([
            'flight_id' => $flight->id,
            'tour_id' => $tour->id,
            'returning' => (bool)$request->get('returning'),
            'date_id' => $date_model->id,
        ]);

        return response($transportation, 201);
    }

    public function deleteTransport(SysTransport $transportation)
    {
        $date = $transportation->getDate();
        if (!$transportation->returning && $date->end != null) {
            return response(['message' => "لطفا ابتدا حمل و نقل برگشت را حذف کنید."], 403);
        }
        if ($transportation->returning) {
            $date->update([
                'end' => null
            ]);
        } else {
            $date->update([
                'start' => null
            ]);
        }
        if ($date->start == null && $date->end == null) {
            $date->delete();
        }

        $transportation->flight->delete();
        $transportation->delete();

        return response()->noContent();
    }
}
