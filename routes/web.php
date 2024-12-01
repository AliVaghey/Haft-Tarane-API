<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Shetabit\Multipay\Exceptions\InvalidPaymentException;
use Shetabit\Multipay\Invoice;
use Shetabit\Payment\Facade\Payment;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

Route::get('pay/balance/increase', [PaymentController::class, 'balanceIncrease']);
Route::get('pay/reservation/{reservation}', [PaymentController::class, 'payReservation']);
Route::get('pay/plane-ticket/{ticket}', [PaymentController::class, 'payPlaneTicket'])->name('payment.planeTicket');
Route::get('payment/verify', [PaymentController::class, 'verifyPayment']);


require __DIR__ . '/auth.php';
require __DIR__ . '/app.php';
