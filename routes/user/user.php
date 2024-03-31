<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;

Route::middleware(['auth:sanctum'])->prefix('user/')->group(function () {

    //------------------------- Profile -------------------------
    Route::get('info', [RegisteredUserController::class, 'getInfo']);
    Route::put('info', [RegisteredUserController::class, 'updateInfo']);

});
