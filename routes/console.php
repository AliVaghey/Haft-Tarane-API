<?php

use App\Http\Controllers\AgencyInfoController;
use App\Schedules\DeleteSpecialTours;
use App\Schedules\ExpireDates;
use App\Schedules\ExpireTours;
use App\Schedules\ResetVisits;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Schedule;

//---Schedules :

Schedule::call(new ResetVisits)->daily();
Schedule::call(new ExpireDates)->dailyAt('00:05');
Schedule::call(new ExpireTours)->dailyAt('00:15');
Schedule::call(new DeleteSpecialTours)->dailyAt('00:30');
Schedule::command('model:prune')->daily();

//---Artisan Commands :

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

Artisan::command('update:availables', function () {
    $tours = \App\Models\Tour::where('status', 'active')->get();
    foreach ($tours as $tour) {
        \App\Http\Controllers\AvailableController::generate($tour);
    }
});

