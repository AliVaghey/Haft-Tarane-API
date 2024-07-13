<?php

namespace App\Http\Controllers;

use App\Models\TourReservation;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Shetabit\Multipay\Exceptions\InvalidPaymentException;
use Shetabit\Multipay\Invoice;
use Shetabit\Payment\Facade\Payment;

class PaymentController extends Controller
{
    public function payReservation(Request $request, TourReservation $reservation)
    {
        $request->validate(['reservation_id' => ['required', 'exists:tour_reservations,id']]);
        if ($request->user()->id != $reservation->user_id) {
            return response(['message' => "این خرید به شما تعلق ندارد."]);
        }
        if ($reservation->isPaid()) {
            return response(['message' => "این رزرویشن قبلا پرداخت شده است."]);
        }

        return Payment::amount($reservation->total_price)->purchase(null,
            function ($driver, $transactionId) use ($request) {
                $transaction = Transaction::create([
                    'user_id' => $request->user()->id,
                    'transaction_id' => $transactionId,
                ]);
                $request->session()->put('transaction_id', $transaction->id);
            }
        )->pay()->render();
    }

    public function verifyReservation(Request $request)
    {
        $transaction_id = Transaction::find($request->session()->get('transactionId'));
        if (!$transaction_id) {
            return "مشکلی پیش آمده است. مبلغ پرداخت شده تا 48 ساعت آینده به حساب شما باز میگردد.";
        }
        try {
            $receipt = Payment::amount(1000)->transactionId($transaction_id)->verify();
            // You can show payment referenceId to the user.
            return redirect();
        } catch (InvalidPaymentException $exception) {
            echo $exception->getMessage();
        }
    }
}
