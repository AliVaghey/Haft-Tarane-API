<?php

namespace App\Http\Controllers;

use App\Models\Config;
use Illuminate\Http\Request;

class ConfigController extends Controller
{
    private function makeVisitConfig()
    {
        $config = Config::where('key', 'visit_count')->get();
        if ($config->isEmpty()) {
            $config = Config::create(['key' => 'visit_count', 'value' => collect(['all' => 0, 'today' => 0])]);
        }
        return $config;
    }

    public function countVisit()
    {
        $config = $this->makeVisitConfig();
        $config->value->put('today', $config->value->get('today') + 1);
        $config->value->put('all', $config->value->get('all') + 1);
        $config->save();
    }

    public function getVisits()
    {
        $config = $this->makeVisitConfig();
        return response($config->value);
    }
}
