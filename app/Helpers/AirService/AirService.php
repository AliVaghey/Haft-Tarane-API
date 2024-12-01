<?php

namespace App\Helpers\AirService;

use App\Models\Airport;
use App\Models\Config;
use App\Models\FlightInfo;
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

    public function getCaptcha($uniqueID)
    {
        $response = Http::post($this->URL . 'flight/captcha', [
            'sessionID' => $this->sessionId,
            'uniqueID' => $uniqueID,
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

    public function reserveTicket($uniqueID, $requestID, $captchaCode, $mobile, $email, $passengers)
    {
        $response = Http::post($this->URL . 'flight/reservation', [
            'sessionID' => $this->sessionId,
            'uniqueID' => $uniqueID,
            'requestID' => $requestID,
            'captchaCode' => $captchaCode,
            'mobile' => $mobile,
            'email' => $email,
            'passengers' => $passengers,
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

    public function buyTicket($voucher, $repeat = 0)
    {
        $response = Http::post($this->URL . 'flight/reservation', [
            'sessionID' => $this->sessionId,
            'voucher' => $voucher,
        ]);

        if ($response->successful()) {
            if ($response->json('Status')) {
                $reference = $response->json('Result')['reference'];
                if ($repeat < 10 && ($reference == 112 || $reference == 110 || $reference == 101 || $reference == 98)) {
                    return $this->buyTicket($voucher, $repeat++);
                } elseif ($repeat >= 10 || $reference == null) {
                    throw new \Exception("مشکلی با صدور بلیط پیش آمده لطفا با پشتیبانی تماس بگیرید.", 123);
                }
                return $response->json('Result');
            } else {
                $this->checkSessionProblems($response);
                throw new \Exception($response->json('Error')['code'] . ': ' . $response->json('Error')['message']);
            }
        } else {
            throw new \Exception("Something went wrong!");
        }
    }

    public function checkFlightAvailability(FlightInfo $flight)
    {
        $available = $this->getAvailabeFlights($flight->from, $flight->to, $flight->date_flight);
        foreach ($available as $f) {
            if (
                $f['number_flight'] == $flight->number_flight &&
                $f['time_flight'] == $flight->time_flight &&
                $f['airline'] == $flight->airline &&
                $f['type_flight'] == $flight->type_flight &&
                $f['IATA_code'] == $flight->IATA_code
            ) {
                if ($f['capacity'] > 0) {
                    $flight->capacity = $f['capacity'];
                    $flight->save();
                    return true;
                } else {
                    return false;
                }
            }
        }
        return false;
    }

    private function checkSessionProblems($response)
    {
        if ($response->json('Error')['code'] == 1013 || $response->json('Error')['code'] == 1014) {
            $this->login(Config::where('key', 'air_service_credentials')->first());
            throw new \Exception("مشکلی با سیستم پرواز پیش آمده. لطفا دوباره تلاش کنید.");
        }
    }
}
