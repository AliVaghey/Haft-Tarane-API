<?php

use App\Http\Controllers\AgencyInfoController;
use App\Http\Controllers\AirportController;
use App\Schedules\ExpireDates;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(new ExpireDates)->twiceDaily();

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
    $superAdmin = User::create([
        'username' => $name,
        'phone' => $phone,
        'access_type' => \App\Enums\UserAccessType::SuperAdmin,
        'password' => Hash::make($password),
    ]);
    AgencyInfoController::makeModel($superAdmin, $superAdmin);

    $this->info("Super-Admin has been created successfully.");
})->purpose('Make new user user.');

