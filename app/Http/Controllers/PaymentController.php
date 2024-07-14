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
        if ($request->user()->id != $reservation->user_id) {
            return response(['message' => "این خرید به شما تعلق ندارد."]);
        }
        if ($reservation->isPaid()) {
            return response(['message' => "این رزرویشن قبلا پرداخت شده است."]);
        }

        return Payment::purchase(
            (new Invoice)->amount($reservation->total_price),
            function ($driver, $transactionId) use ($request, $reservation) {
                $transaction = Transaction::firstOrCreate([
                    'user_id' => $request->user()->id,
                    'transaction_id' => $transactionId,
                    'type' => 'tour_reservation',
                    'object_id' => $reservation->id
                ]);
                session(['transaction_id' => $transaction->id]);
            }
        )->pay()->render();
    }

    public function verifyPayment(Request $request)
    {
        $transaction = Transaction::find(session('transaction_id'));
        if (!$transaction) {
            echo "مشکلی پیش آمده است. مبلغ پرداخت شده تا 48 ساعت آینده به حساب شما باز میگردد.";
            return null;
        }
        $object = $transaction->getObject();
        session()->forget('transaction_id');
        try {
            $receipt = Payment::amount($object->total_price)->transactionId($transaction->transaction_id)->verify();
            $transaction->update([
                'reference_id' => $receipt->getReferenceId(),
                'status' => 'successful'
            ]);
            $object->update([
                'status' => 'paid',
                'transaction_id' => $transaction->id
            ]);
            return redirect(env('FRONTEND_URL') . '/fa/user/tours');

        } catch (InvalidPaymentException $exception) {
            $transaction->update(['status' => 'failed']);
            echo $exception->getMessage();
            return null;
        }
    }
}
