<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlaceController;

Route::middleware(['auth:sanctum'])->prefix('admin/')->group(function () {

    Route::post('city', [PlaceController::class, 'create']);
    Route::get('cities', [PlaceController::class, 'getAllPlaces']);
    Route::delete('city', [PlaceController::class, 'deleteCity']);
});
