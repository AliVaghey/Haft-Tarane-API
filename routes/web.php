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

Route::post('pay/{reservation}', [PaymentController::class, 'payReservation']);
Route::get('payment/verify', [PaymentController::class, 'verifyReservation']);

Route::get('payment', function (Request $request) {
    return Payment::purchase(
        (new Invoice)->amount(200),
        function ($driver, $transactionId) use ($request) {
            // Store transactionId in database.
            // We need the transactionId to verify payment in the future.
            $request->session()->put('transactionId', $transactionId);
        }
    )->pay()->render();
});

Route::get('payment/verify', function (Request $request) {
    $transaction_id = $request->session()->get('transactionId');
    try {
        $receipt = Payment::amount(1000)->transactionId($transaction_id)->verify();
        // You can show payment referenceId to the user.
        return redirect('https://bibaksafar.com/fa');
//        echo $receipt->getReferenceId();

    } catch (InvalidPaymentException $exception) {
        echo $exception->getMessage();
    }
})->name('payment.verify');

require __DIR__ . '/auth.php';
