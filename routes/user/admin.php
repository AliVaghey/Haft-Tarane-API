<?php


use App\Http\Controllers\AdsController;
use App\Http\Controllers\AirportController;
use App\Http\Controllers\CheckOutController;
use App\Http\Controllers\OptionsController;
use App\Http\Controllers\ProfitRateController;
use App\Http\Controllers\SpecialTourController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\AgencyInfoController;
use App\Http\Controllers\TourController;

Route::middleware(['auth:sanctum', 'isAdmin'])->prefix('admin/')->group(function () {

    //*********************************** Super Admin ***********************************
    //----------------------------- ads -----------------------------
    Route::get('banners', [AdsController::class, 'all'])->middleware('superAdmin');
    Route::get('banner/{ad}', [AdsController::class, 'read'])->middleware('superAdmin');
    Route::post('banner', [AdsController::class, 'create'])->middleware('superAdmin');
    Route::put('banner/{ad}', [AdsController::class, 'update'])->middleware('superAdmin');
    Route::delete('banner/{ad}', [AdsController::class, 'delete'])->middleware('superAdmin');

    //----------------------- Airports service ----------------------
    Route::get('save-airports', [AirportController::class, 'getAirports'])->middleware('superAdmin');

    //-------------------------- Profit Rate ------------------------
    Route::post('profit-rate', [ProfitRateController::class, 'create'])->middleware('superAdmin');
    Route::put('profit-rate/{rate}', [ProfitRateController::class, 'edit'])->middleware('superAdmin');
    Route::delete('profit-rate/{rate}', [ProfitRateController::class, 'delete'])->middleware('superAdmin');
    Route::get('profit-rates', [ProfitRateController::class, 'all'])->middleware('superAdmin');
    Route::get('profit-rate/{rate}', [ProfitRateController::class, 'read'])->middleware('superAdmin');

    //---------------------------- Options --------------------------
    Route::post('option', [OptionsController::class, 'add'])->middleware('superAdmin');
    Route::delete('option/{option}', [OptionsController::class, 'remove'])->middleware('superAdmin');

    //------------------------ Special Tours -------------------------
    Route::post('tour/{tour}/special', [SpecialTourController::class, 'create'])->middleware('superAdmin');
    Route::post('special-tour/{tour}', [SpecialTourController::class, 'edit'])->middleware('superAdmin');
    Route::delete('special-tour/{tour}', [SpecialTourController::class, 'delete'])->middleware('superAdmin');
    Route::get('special-tours', [SpecialTourController::class, 'getAll'])->middleware('superAdmin');
    Route::get('special-tour/{tour}', [SpecialTourController::class, 'read'])->middleware('superAdmin');


    //====================================== Admin ========================================
    //------------------------- Profile Info -------------------------
    Route::get('info', [RegisteredUserController::class, 'getInfo']);
    Route::put('info', [RegisteredUserController::class, 'updateInfo']);

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
    Route::get('hotel/{hotel}', [HotelController::class, 'read']);
    Route::put('hotel/{id}', [HotelController::class, 'edit']);
    Route::delete('hotel/{id}', [HotelController::class, 'delete']);
    Route::post('hotel/{id}/photos', [HotelController::class, 'uploadGallery']);
    Route::delete('hotel/{hotel_id}/photo/{photo_id}', [HotelController::class, 'deleteFromGallery']);

    //---------------------- Agency Management -----------------------
    Route::get('agency/{user}', [AgencyInfoController::class, 'read']);
    Route::get('agencies', [AgencyInfoController::class, 'getAll']);
    Route::get('my-agencies', [AgencyInfoController::class, 'getMyAgencies']);

    //----------------------- Tour Management ------------------------
    Route::get('tour/{id}', [TourController::class, 'read']);
    Route::get('active-tours', [TourController::class, 'activeTours']);
    Route::get('my-tours', [TourController::class, 'adminMyTours']);
    Route::get('my-pending-tours', [TourController::class, 'adminPendingTours']);
    Route::post('tour/{id}/approve', [TourController::class, 'approve']);
    Route::post('tour/{id}/reject', [TourController::class, 'reject']);

    //-------------------------- CheckOuts --------------------------
    Route::get('agencies/checkouts', [CheckoutController::class, 'getAgencies']);
    Route::get('agency/{agency}/sales', [CheckoutController::class, 'getAgencySales']);
    Route::get('agency/{agency}/checkout', [CheckoutController::class, 'getAgencyCheckout']);
    Route::post('agency/{agency}/checkout', [CheckoutController::class, 'checkOut']);
    Route::get('agency/{agency}/checkouts', [CheckoutController::class, 'getAgencyCheckouts']);
    Route::get('checkout/{checkout}', [CheckOutController::class, 'getCheckOutsDetails']);

    //-------------------------- Statistics --------------------------
    Route::get('dashboard/info', [UserController::class, 'adminDashboardInfo']);
});
