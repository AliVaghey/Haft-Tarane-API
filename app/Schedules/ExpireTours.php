<?php

namespace App\Schedules;


use App\Enums\TourStatus;
use App\Models\Tour;

class ExpireTours
{
    public function __invoke()
    {
        $tours = Tour::where('status', 'active')->get();
        foreach ($tours as $tour) {
            $f = true;
            foreach($tour->dates as $date) {
                if (!$date->expired) {
                    $f = false;
                    break;
                }
            }
            if ($f) {
                $tour->update(['status' => TourStatus::Expired]);
            }
        }
    }
}
