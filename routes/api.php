<?php

use App\Http\Controllers\AdsController;
use App\Http\Controllers\AirportController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\FlightInfoController;
use App\Http\Controllers\OptionsController;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\SpecialTourController;
use App\Http\Controllers\TourController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NavigationsController;

require __DIR__ . '/user/admin.php';
require __DIR__ . '/user/agency.php';
require __DIR__ . '/user/user.php';


Route::get('cities', [PlaceController::class, 'getAllPlaces']);
Route::get('hotel-cities', [PlaceController::class, 'getAllHotelPlaces']);
Route::get('tour-origin', [PlaceController::class, 'getAllTourOrigin']);
Route::get('tour-destination', [PlaceController::class, 'getAllTourDestination']);

Route::get('panel', [NavigationsController::class, 'redirectPanel'])->middleware(['auth:sanctum'])->name('panel');
Route::get('airports', [AirportController::class, 'AllAirports']);
Route::post('flights', [FlightInfoController::class, 'availableFlights']);

Route::get('options', [OptionsController::class, 'searchOptions']);

Route::get('tours', [TourController::class, 'PublicGetTours']);
Route::get('tour/{tour}', [TourController::class, 'getActiveTour']);
Route::get('tours/nature', [TourController::class, 'publicNatureTours']);
Route::get('tours/hotel', [TourController::class, 'PublicGetHotelTours']);
Route::get('cost/{cost}', [TourController::class, 'getCostInfo']);
Route::get('similar-dates', [TourController::class, 'similarDates']);
Route::get('close-dates', [TourController::class, 'closeDates']);

Route::get('specials', [SpecialTourController::class, 'getAll']);
Route::get('special/{tour}', [SpecialTourController::class, 'getSpecialTourCosts']);
Route::get('banners', [AdsController::class, 'all']);

Route::post('visit', [ConfigController::class, 'countVisit']);
Route::get('visits', [ConfigController::class, 'getVisits']);

