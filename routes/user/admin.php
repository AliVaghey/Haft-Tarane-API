<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\HotelController;

Route::middleware(['auth:sanctum', 'isAdmin'])->prefix('admin/')->group(function () {

    //------------------------- Profile Info -------------------------
    Route::get('info', [RegisteredUserController::class, 'getInfo']);

    //----------------------- Place Management -----------------------
    Route::post('city', [PlaceController::class, 'create']);
    Route::delete('city/{id}', [PlaceController::class, 'deleteCity']);
    Route::get('cities', [PlaceController::class, 'getAllPlaces']);
    Route::put('city/{id}', [PlaceController::class, 'edit']);

    //------------------------- User Control -------------------------
    Route::get('user/{id}', [UserController::class, 'getUser']);
    Route::put('user/{id}', [UserController::class, 'updateUser']);
    Route::get('users', [UserController::class, 'getUserList']);
    Route::patch('user/{id}/access', [UserController::class, 'changeAccess']);

    //----------------------- Hotel Management -----------------------
    Route::get('hotels', [HotelController::class, 'getAll']);
    Route::get('my-hotels', [HotelController::class, 'myHotels']);
    Route::post('hotel', [HotelController::class, 'create']);
    Route::put('hotel/{id}', [HotelController::class, 'edit']);
    Route::delete('hotel/{id}', [HotelController::class, 'delete']);

});
