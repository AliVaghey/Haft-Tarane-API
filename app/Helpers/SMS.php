<?php

namespace App\Helpers;

class SMS
{
    private $username;
    private $api_key;
    private $number;

    public function __construct()
    {

        $this->username = config('sms.username');
        $this->api_key = config('sms.key');
        $this->number = config('sms.number');
    }

    public function send($number, $message)
    {

    }
}

function sms()
{
    return new SMS();
}
