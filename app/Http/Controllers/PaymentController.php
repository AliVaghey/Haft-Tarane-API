<?php

namespace App\Http\Controllers;

use App\Models\BalanceIncrease;
use App\Models\PlaneTickt;
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

    public function payPlaneTicket(Request $request, PlaneTickt $tickt)
    {
        if ($tickt->status != 'pending') {
            return response(['message' => "این بلیط منقضی شده است."]);
        }

        return Payment::purchase(
            (new Invoice)->amount($tickt->total_price),
            function ($driver, $transactionId) use ($request, $tickt) {
                $transaction = Transaction::firstOrCreate([
                    'user_id' => $request->user()->id,
                    'transaction_id' => $transactionId,
                    'type' => PlaneTickt::class,
                    'object_id' => $tickt->id
                ]);
                session(['transaction_id' => $transaction->id]);
            }
        )->pay()->render();
    }

    public function balanceIncrease(Request $request)
    {
        if (!($amount = (int)$request->query('amount'))) {
            abort(422, "مبلغ مورد نظر مشخص نشده است!");
        }
        if ($amount < 1000 || $amount > 49999999) {
            abort(422, "مبلغ وارد شده از محدوده مجاز درگاه پرداخت خارج می باشد!");
        }

        if (!$request->user()->id) {
            abort(500, "User not detected!");
        }
        $increase = BalanceIncrease::create([
            'user_id' => $request->user()->id,
            'amount' => $amount,
        ]);

        return Payment::purchase(
            (new Invoice)->amount($amount),
            function ($driver, $transactionId) use ($increase) {
                $transaction = Transaction::firstOrCreate([
                    'user_id' => $increase->user_id,
                    'transaction_id' => $transactionId,
                    'type' => BalanceIncrease::class,
                    'object_id' => $increase->id
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

            //only add params to the end of list. DO NOT CHANGE THE ORDER!!!.
            $object->paid($transaction->id);

            $redirect_url = $request->query('url', env('FRONTEND_URL') . '/fa/user/tours');
            return redirect($redirect_url);

        } catch (InvalidPaymentException $exception) {
            $transaction->update(['status' => 'failed']);
            $object->paymentFailed();
            echo $exception->getMessage();
            return null;
        }
    }
}
