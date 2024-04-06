<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AgencyInfoController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\SupportController;

Route::middleware(['auth:sanctum', 'isAgency'])->prefix('agency/')->group(function () {

    //------------------------- Profile Info -------------------------
    Route::get('info', [AgencyInfoController::class, 'getInfo']);
    Route::put('info', [AgencyInfoController::class, 'updateOrMake']);

    //------------------------ Tour Management -----------------------
    Route::post('tour', [TourController::class, 'create']);
    Route::get('tour/{id}', [TourController::class, 'read']);
    Route::put('tour/{id}', [TourController::class, 'update']);
    Route::delete('tour/{id}', [TourController::class, 'delete']);
    Route::put('tour/{id}/certificates', [TourController::class, 'updateCertificate']);

    //------------------------- Support Team -------------------------
    Route::post('support', [SupportController::class, 'new']);
    Route::get('supports', [SupportController::class, 'getAll']);
    Route::put('support/{id}', [SupportController::class, 'edit']);
    Route::delete('support/{id}', [SupportController::class, 'delete']);

});
