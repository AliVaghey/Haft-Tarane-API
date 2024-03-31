<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\AgencyInfoController;

Route::middleware(['auth:sanctum', 'isAgency'])->prefix('agency/')->group(function () {

    //------------------------- Profile Info -------------------------
    Route::get('info', [AgencyInfoController::class, 'getInfo']);
    Route::put('info', [AgencyInfoController::class, 'updateOrMake']);

});
