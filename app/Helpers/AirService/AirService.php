<?php

namespace App\Helpers\AirService;

use App\Models\Airport;
use App\Models\Config;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class AirService
{
    private $sessionId;
    private $URL = "https://safartik.ir/api/";
    private $username;
    private $password;

    public function __construct()
    {
        $this->username = config('apis.air_service.username');
        $this->password = config('apis.air_service.password');

        $config =
            Config::where('key', 'air_service_credentials')->first() ??
            Config::create(['key' => 'air_service_credentials', 'value' => collect(['expiration' => null, 'sessionId' => null])]);
        $exp = new Carbon($config->value->get('expiration'));
        if (now() < $exp) {
            $this->sessionId = $config->value->get('sessionId');
        } else {
            $this->login($config);
        }
    }

    private function login(Config $config)
    {
        $response = Http::post($this->URL . 'login', [
            'username' => $this->username,
            'password' => $this->password,
        ]);
        if ($response->successful()) {
            if ($response->json('Status')) {
                $this->sessionId = $response->json('Result')['sessionID'];
                $config->value->put('sessionId', $this->sessionId);
                $config->value->put('expiration', now()->addhours(5));
                $config->save();
            } else {
                throw new \Exception($response->json('Error')['code'] . ': ' . $response->json('Error')['message']);
            }
        } else {
            throw new \Exception("Something went wrong!");
        }
    }

    public function getAirports()
    {
        $response = Http::post($this->URL . 'flight/airports', [
            'sessionID' => $this->sessionId,
        ]);
        if ($response->successful()) {
            if ($response->json('Status')) {
                $airports = $response->json('Result');
                foreach ($airports as $airport) {
                    Airport::create([
                        'IATA_code' => $airport['IATA_code'],
                        'name_en' => $airport['name_en'],
                        'name_fa' => $airport['name_fa'],
                    ]);
                }
            } else {
                $this->checkSessionProblems($response);
                throw new \Exception($response->json('Error')['code'] . ': ' . $response->json('Error')['message']);
            }
        } else {
            throw new \Exception("Something went wrong!");
        }
    }

    public function getAvailabeFlights($from, $to, $date)
    {
        $response = Http::post($this->URL . 'flight/available', [
            'sessionID' => $this->sessionId,
            'from' => $from,
            'to' => $to,
            'date' => $date,
        ]);

        if ($response->successful()) {
            if ($response->json('Status')) {
                return $response->json('Result');
            } else {
                $this->checkSessionProblems($response);
                throw new \Exception($response->json('Error')['code'] . ': ' . $response->json('Error')['message']);
            }
        } else {
            throw new \Exception("Something went wrong!");
        }
    }

    private function checkSessionProblems($response)
    {
        if ($response->json('Error')['code'] == 1013 || $response->json('Error')['code'] == 1014) {
            $this->login(Config::where('key', 'air_service_credentials')->first());
            throw new \Exception("مشکلی با سیستم پرواز پیش آمده. لطفا دوباره تلاش کنید.");
        }
    }
}
