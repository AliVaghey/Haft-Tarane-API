<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('new_admin', function () {
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
        'access_type' => \App\Enums\UserAccessType::Admin,
        'password' => Hash::make($password),
    ]);

    $this->info("Admin User has been created successfully.");
})->purpose('Make new user user.');
