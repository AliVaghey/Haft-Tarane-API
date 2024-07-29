<?php

use App\Http\Controllers\CheckOutController;
use App\Http\Controllers\PriceChangeController;
use App\Http\Controllers\ProfitRateController;
use App\Http\Controllers\ReservationFileController;
use App\Http\Controllers\SysTransportController;
use App\Http\Controllers\TourReservationController;
use App\Http\Controllers\TransportationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AgencyInfoController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\CostsController;
use App\Http\Controllers\DateController;

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
    Route::put('tour/{id}/pending', [TourController::class, 'setPending']);
    Route::put('tour/{id}/draft', [TourController::class, 'setToDraft']);
    Route::get('tour/{id}/messages', [TourController::class, 'getMessages']);
    Route::get('tours', [TourController::class, 'getTours']);
    Route::post('tour/{tour_id}/cost/{hotel_id}', [CostsController::class, 'addCost']);
    Route::delete('tour/cost/{id}', [CostsController::class, 'deleteCost']);
    Route::put('tour/cost/{cost}', [CostsController::class, 'updateCost']);
    Route::post('tour/{id}/date', [DateController::class, 'addDate']);
    Route::put('tour/date/{date}', [DateController::class, 'updateDate']);
    Route::delete('tour/date/{id}', [DateController::class, 'deleteDate']);
    Route::post('tour/{tour}/transportation', [TransportationController::class, 'addTransport']);
    Route::put('tour/transportation/{transportation}', [TransportationController::class, 'updateTransport']);
    Route::delete('tour/transportation/{transportation}', [TransportationController::class, 'deleteTransport']);
    Route::post('tour/{tour}/sys-transportation', [SysTransportController::class, 'addTransport']);
    Route::delete('tour/sys-transportation/{transportation}', [SysTransportController::class, 'deleteTransport']);
    Route::post('date/{date}/expiration', [DateController::class, 'updateExpiration']);

    Route::post('date/{date}/cost/{cost}/price-change', [PriceChangeController::class, 'add']);
    Route::post('date/{date}/price-change', [PriceChangeController::class, 'addAll']);
    Route::put('price-change/{price_change}', [PriceChangeController::class, 'update']);
    Route::delete('price-change/{price_change}', [PriceChangeController::class, 'delete']);
    Route::delete('tour/{tour}/price-change/all', [PriceChangeController::class, 'deleteAll']);

    Route::post('tour/{tour}/copy', [TourController::class, 'copy']);

    //------------------------- Support Team -------------------------
    Route::post('support', [SupportController::class, 'new']);
    Route::get('supports', [SupportController::class, 'getAll']);
    Route::get('support/{support}', [SupportController::class, 'read']);
    Route::put('support/{id}', [SupportController::class, 'edit']);
    Route::delete('support/{id}', [SupportController::class, 'delete']);

    //---------------------------- Hotels ----------------------------
    Route::get('hotels', [HotelController::class, 'GetAll']);

    //-------------------------- Profit Rates ------------------------
    Route::get('profit-rates', [ProfitRateController::class, 'all']);
    Route::get('profit-rate/{rate}', [ProfitRateController::class, 'read']);

    //-------------------------- Reservations ------------------------
    Route::get('reservations', [TourReservationController::class, 'getAgencyReservations']);
    Route::get('reservation/{reservation}', [TourReservationController::class, 'getAgencyReservation']);
    Route::post('reservation/{reservation}/change-status', [TourReservationController::class, 'changeReservationStatus']);
    Route::post('reservation/{reservation}/files', [ReservationFileController::class, 'upload']);
    Route::delete('reservation/{reservation}/files', [ReservationFileController::class, 'remove']);

    //--------------------------- Checkouts --------------------------
    Route::get('checkouts', [CheckoutController::class, 'getMyCheckoutsForAgency']);
    Route::get('checkout/{checkout}', [CheckoutController::class, 'getSaleCheckoutsForAgency']);
    Route::get('not-checkouts', [CheckoutController::class, 'getNotMyCheckoutsForAgency']);

    //-------------------------- Statistics --------------------------
    Route::get('dashboard/info', [AgencyInfoController::class, 'dashboardInfo']);

});
