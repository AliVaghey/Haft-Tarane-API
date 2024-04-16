<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AgencyInfoController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\CostsController;

Route::middleware(['auth:sanctum', 'isAgency'])->prefix('agency/')->group(function () {

    //------------------------- Profile Info -------------------------
    Route::get('info', [AgencyInfoController::class, 'getInfo']);
    Route::put('info', [AgencyInfoController::class, 'updateOrMake']);

    //------------------------ Tour Management -----------------------
    Route::post('tour', [TourController::class, 'create']);
    Route::get('tour/{id}', [TourController::class, 'read']);
    Route::put('tour/{id}', [TourController::class, 'update']);
    Route::delete('tour/{id}', [TourController::class, 'delete']);
    Route::post('tour/certificates', [TourController::class, 'updateCertificate']);
    Route::post('tour/{id}/hotel', [TourController::class, 'linkHotel']);
    Route::delete('tour/{id}/hotel', [TourController::class, 'unlinkHotel']);
    Route::post('tour/{id}/date', [TourController::class, 'addDateAndPending']);
    Route::put('tour/{id}/draft', [TourController::class, 'setToDraft']);
    Route::get('tour/{id}/messages', [TourController::class, 'getMessages']);
    Route::get('tours', [TourController::class, 'getTours']);
    Route::post('tour/{tour_id}/cost/{hotel_id}', [CostsController::class, 'addCost']);
    Route::delete('tour/cost/{id}', [CostsController::class, 'deleteCost']);


    //------------------------- Support Team -------------------------
    Route::post('support', [SupportController::class, 'new']);
    Route::get('supports', [SupportController::class, 'getAll']);
    Route::put('support/{id}', [SupportController::class, 'edit']);
    Route::delete('support/{id}', [SupportController::class, 'delete']);

    //---------------------------- Hotels ----------------------------
    Route::get('hotels', [HotelController::class, 'GetAll']);

});
