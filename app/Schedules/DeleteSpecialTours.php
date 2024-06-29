<?php

namespace App\Schedules;


use App\Models\Date;
use App\Models\SpecialTour;

class DeleteSpecialTours
{
    public function __invoke()
    {
        $tours = SpecialTour::all();
        foreach ($tours as $tour) {
            foreach ($tour->dates as $key => $id) {
                $date = Date::find($id);
                if ($date->expired) {
                    $tour->dates->forget($key);
                }
            }
            if ($tour->dates->isEmpty()) {
                $tour->removePhoto();
                $tour->delete();
            }
        }
    }
}
