<?php

use App\Http\Controllers\PlaceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NavigationsController;

require __DIR__ . '/user/admin.php';
require __DIR__ . '/user/agency.php';

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('cities', [PlaceController::class, 'getAllPlaces']);

Route::get('panel', [NavigationsController::class, 'redirectPanel'])->middleware(['auth:sanctum'])->name('panel');
