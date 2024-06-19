<?php

use App\Http\Controllers\TourReservationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;

Route::middleware(['auth:sanctum'])->prefix('user/')->group(function () {

    //------------------------- Profile -------------------------
    Route::get('info', [RegisteredUserController::class, 'getInfo']);
    Route::put('info', [RegisteredUserController::class, 'updateInfo']);

    //--------------------- Tour Reservation --------------------
    Route::post('tour/{tour}/date/{date}/cost/{cost}/reserve', [TourReservationController::class, 'reserve']);
    Route::get('reservations', [TourReservationController::class, 'getReservations']);
    Route::get('reservation/{id}', [TourReservationController::class, 'getReservation']);

});
