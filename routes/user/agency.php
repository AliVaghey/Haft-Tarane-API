<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'isAgency'])->prefix('agency/')->group(function () {

});
