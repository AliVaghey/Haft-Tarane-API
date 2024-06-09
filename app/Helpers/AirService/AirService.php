<?php

namespace App\Helpers\AirService;

use App\Models\Config;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class AirService
{
    private $sessionId;
    private $loginURL = "https://safartik.ir/api/login";
    private $username;
    private $password;

    public function __construct()
    {
        $this->username = config('apis.air_service.username');
        $this->password = config('apis.air_service.password');

        $config =
            Config::where('key', 'air_service_credentials')->first() ??
            Config::create(['key' => 'air_service_credentials', 'value' => collect(['expiration' => '', 'sessionId' => ''])]);
        $exp = new Carbon($config->value->get('expiration'));
        if (now() < $exp) {
            $this->sessionId = $config->value->get('sessionId');
        } else {
            $this->login($config);
        }
    }

    private function login(Config $config)
    {
        $response = Http::post($this->loginURL, [
            'username' => $this->username,
            'password' => $this->password,
        ]);
        if ($response->successful()) {

        }
    }
}
