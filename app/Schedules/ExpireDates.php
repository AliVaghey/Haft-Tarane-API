<?php

namespace App\Schedules;

use App\Models\Date;
use Carbon\Carbon;

class ExpireDates
{
    public function __invoke()
    {
        $dates = Date::where('expire', '=', false)->get();
        foreach ($dates as $date) {
            $start = new Carbon($date->start);
            if ($start->subDays($date->tour->expiration) < now()) {
                $date->update(['expire' => true]);
            }
        }
        return true;
    }
}
