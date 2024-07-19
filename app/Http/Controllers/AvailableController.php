<?php

namespace App\Http\Controllers;

use App\Enums\TourStatus;
use App\Models\Available;
use App\Models\Costs;
use App\Models\Date;
use App\Models\PriceChange;
use App\Models\SysTransport;
use App\Models\Tour;
use Illuminate\Http\Request;

class AvailableController extends Controller
{
    static public function generate(Tour $tour)
    {
        if ($tour->status != TourStatus::Active) {
            return;
        }

        foreach ($tour->dates as $date) {
            foreach ($tour->costs as $cost) {
                Available::updateOrCreate([
                    'tour_id' => $tour->id,
                    'date_id' => $date->id,
                    'cost_id' => $cost->id,
                ], [
                    'min_cost' => self::calculateMinCost($tour, $date, $cost),
                    'expired' => !(!$date->expired && $tour->status == TourStatus::Active)
                ]);
            }
        }
    }

    static public function deactive(Tour $tour)
    {
        if ($tour->status == TourStatus::Active) {
            return;
        }

        foreach ($tour->dates as $date) {
            foreach ($tour->costs as $cost) {
                Available::updateOrCreate([
                    'tour_id' => $tour->id,
                    'date_id' => $date->id,
                    'cost_id' => $cost->id,
                ], [
                    'expired' => true
                ]);
            }
        }
    }

    static private function calculateMinCost(Tour $tour, Date $date, Costs $cost)
    {
        if ($tour->isSysTrans()) {
            $total_price = $cost->two_bed;
            foreach (SysTransport::where('date_id', $date->id)->get() as $transport) {
                $total_price += ($transport->flight->price_final / 10);
            }
        } else {
            $total_price = $cost->two_bed;
        }
        $price_change = PriceChange::where('date_id', $date->id)->where('cost_id', $cost->id)->first();
        if ($price_change) {
            $total_price += $price_change->two_bed;
        }
        return $total_price;
    }
}
