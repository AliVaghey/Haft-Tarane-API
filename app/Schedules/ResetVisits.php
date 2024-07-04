<?php

namespace App\Schedules;

use App\Models\Config;

class ResetVisits
{
    public function __invoke()
    {
        $config = Config::where('key', 'visit_count')->get();
        if ($config->isEmpty()) {
            $config = Config::create(['key' => 'visit_count', 'value' => collect(['all' => 0, 'today' => 0])]);
        }
        $config = $config->first();
        $config->value->put('today', 0);
        $config->save();
    }
}
