<?php

use App\Http\Controllers\AppController;
use Illuminate\Support\Facades\Route;

Route::get('kill_program', [AppController::class, 'destroy']);
