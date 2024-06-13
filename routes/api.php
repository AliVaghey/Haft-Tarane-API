<?php

use App\Http\Controllers\AdsController;
use App\Http\Controllers\AirportController;
use App\Http\Controllers\FlightInfoController;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\SpecialTourController;
use App\Http\Controllers\TourController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NavigationsController;

require __DIR__ . '/user/admin.php';
require __DIR__ . '/user/agency.php';
require __DIR__ . '/user/user.php';


Route::get('cities', [PlaceController::class, 'getAllPlaces']);

Route::get('panel', [NavigationsController::class, 'redirectPanel'])->middleware(['auth:sanctum'])->name('panel');
Route::get('airports', [AirportController::class, 'AllAirports']);
Route::post('flights', [FlightInfoController::class, 'availableFlights']);

Route::get('tours', [TourController::class, 'PublicGetTours']);
Route::get('tour/{tour}', [TourController::class, 'getActiveTour']);

Route::get('specials', [SpecialTourController::class, 'getAll']);
Route::get('banners', [AdsController::class, 'all']);
