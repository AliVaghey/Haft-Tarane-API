<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\AgencyInfoController;
use App\Http\Controllers\TourController;

Route::middleware(['auth:sanctum', 'isAdmin'])->prefix('admin/')->group(function () {

    //------------------------- Profile Info -------------------------
    Route::get('info', [RegisteredUserController::class, 'getInfo']);

    //----------------------- Place Management -----------------------
    Route::post('city', [PlaceController::class, 'create']);
    Route::get('city/{city}', [PlaceController::class, 'read']);
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
    Route::post('hotel/{id}/photos', [HotelController::class, 'uploadGallery']);
    Route::delete('hotel/{hotel_id}/photo/{photo_id}', [HotelController::class, 'deleteFromGallery']);

    //---------------------- Agency Management -----------------------
    Route::get('agencies', [AgencyInfoController::class, 'getAll']);
    Route::get('my-agencies', [AgencyInfoController::class, 'getMyAgencies']);

    //----------------------- Tour Management ------------------------
    Route::get('tour/{id}', [TourController::class, 'read']);
    Route::get('active-tours', [TourController::class, 'activeTours']);
    Route::get('my-tours', [TourController::class, 'adminMyTours']);
    Route::get('my-pending-tours', [TourController::class, 'adminPendingTours']);
    Route::post('tour/{id}/approve', [TourController::class, 'approve']);
    Route::post('tour/{id}/reject', [TourController::class, 'reject']);

});
