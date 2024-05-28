<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('make:superadmin', function () {
    $name = $this->ask('Enter your username');
    $phone = $this->ask('Enter your phone');
    $password = $this->secret('Enter your password');
    $confirmation = $this->secret('Please repeat your password');
    while ($password != $confirmation) {
        $this->error('Your confirmation dose not match with password. Please try again:');
        $password = $this->secret('Enter your password');
        $confirmation = $this->secret('Please repeat your password');
    }
    User::create([
        'username' => $name,
        'phone' => $phone,
        'access_type' => \App\Enums\UserAccessType::SuperAdmin,
        'password' => Hash::make($password),
    ]);

    $this->info("Super-Admin has been created successfully.");
})->purpose('Make new user user.');

Artisan::command('lorem:send', function () {

    ini_set("soap.wsdl_cache_enabled", "0");
    $sms_client = new SoapClient('http://api.payamak-panel.com/post/send.asmx?wsdl', array('encoding' => 'UTF-8'));

    $parameters['username'] = "avaparvaz";
    $parameters['password'] = "ec52ddd5-56f7-4026-8c26-edf4afa99d2e";
    $parameters['to'] = "9399412613";
    $parameters['from'] = "500010006601918";
    $parameters['text'] = "تست";
    $parameters['isflash'] = false;

    echo $sms_client->SendSimpleSMS2($parameters)->SendSimpleSMS2Result;
});
