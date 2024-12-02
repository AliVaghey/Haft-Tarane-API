<?php

use App\Http\Controllers\BalanceIncreaseController;
use App\Http\Controllers\PlaneTicktController;
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
    Route::get('reservation/{reservation}', [TourReservationController::class, 'getReservation']);
    Route::delete('reservation/{reservation}', [TourReservationController::class, 'deleteReservation']);

    //--------------------- Balance Increase --------------------
    Route::get('balance-increases', [BalanceIncreaseController::class, 'getAll']);


});
