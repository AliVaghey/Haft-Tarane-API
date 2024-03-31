<?php

use App\Http\Controllers\PlaceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NavigationsController;
use App\Http\Resources\UserResource;

require __DIR__ . '/user/admin.php';
require __DIR__ . '/user/agency.php';
require __DIR__ . '/user/user.php';


Route::get('cities', [PlaceController::class, 'getAllPlaces']);

Route::get('panel', [NavigationsController::class, 'redirectPanel'])->middleware(['auth:sanctum'])->name('panel');
