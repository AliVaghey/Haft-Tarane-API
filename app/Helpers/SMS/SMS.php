<?php

namespace App\Helpers\SMS;

use SoapClient;

class SMS
{
    private $username;
    private $api_key;
    private $number;

    private $client;
    private $isflash;

    public function __construct(bool $isflash = false)
    {
        ini_set("soap.wsdl_cache_enabled", "0");
        $this->client = new SoapClient('http://api.payamak-panel.com/post/send.asmx?wsdl', array('encoding' => 'UTF-8'));
        $this->username = config('sms.username');
        $this->api_key = config('sms.key');
        $this->number = config('sms.number');
        $this->isflash = $isflash;
    }

    public function send($number, $message)
    {
        return $this->client->SendSimpleSMS2([
            'username' => $this->username,
            'password' => $this->api_key,
            'to' => $number,
            'from' => $this->number,
            'text' => $message,
            'isflash' => $this->isflash
        ])->SendSimpleSMS2Result;
    }
}

