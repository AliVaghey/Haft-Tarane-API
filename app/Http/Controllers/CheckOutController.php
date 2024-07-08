<?php

namespace App\Http\Controllers;

use App\Http\Resources\AgenciesCheckOutResource;
use App\Http\Resources\CheckoutResource;
use App\Http\Resources\TourReservationResource;
use App\Models\AgencyInfo;
use App\Models\CheckOut;
use App\Models\TourReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CheckOutController extends Controller
{
    public function getAgencies(Request $request)
    {
        return AgenciesCheckOutResource::collection($request->user()->agencies()->paginate(10));
    }

    public function getAgencySales(Request $request, AgencyInfo $agency)
    {
        return TourReservationResource::collection(
            TourReservation::where('agency_id', $agency->id)
                ->where('status', 'paid')
                ->orderBy('updated_at', 'desc')
                ->paginate(
                    $request->query('per_page', 10)
                )
        );
    }

    private function calculateAgencySales(Request $request, AgencyInfo $agency)
    {
        if ($request->query('start') && $request->query('end')) {
            $sales = TourReservation::where('agency_id')
                ->where('status', 'paid')
                ->whereBetween('created_at', [$request->query('start'), $request->query('end')])
                ->get();
        } else {
            $sales = TourReservation::where('agency_id', $agency->id)->where('status', 'paid')->get();
        }

        $debt = 0;
        $profit = 0;
        $total = 0;
        foreach ($sales as $sale) {
            $debt += $sale->total_price - ($sale->tour->profit_rate * $sale->total_price / 100);
            $profit += $sale->total_price * ($sale->tour->profit_rate / 100);
            $total += $sale->total_price;
        }

        return [
            'sales' => $sales,
            'debt' => $debt,
            'profit' => $profit,
            'total' => $total,
        ];
    }

    public function getAgencyCheckout(Request $request, AgencyInfo $agency)
    {
        $t = $this->calculateAgencySales($request, $agency);
        Arr::pull($t, 'sales');
        return response($t, 200);
    }

    public function checkOut(Request $request, AgencyInfo $agency)
    {
        $numbers = $this->calculateAgencySales($request, $agency);
        $sales = Arr::pull($numbers, 'sales');
        $path = null;
        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store('receipts', 'public');
        }
        $checkout = Checkout::create([
            'agency_id' => $agency->id,
            'admin_id' => $request->user()->id,
            'total_price' => $numbers['debt'],
            'profit' => $numbers['profit'],
            'real_price' => $numbers['total'],
            'receipt' => $path,
            'description' => $request->get('description')
        ]);
        $checkout->refresh();

        DB::table('tour_reservations')
            ->whereIn('id', $sales->map(fn($sale) => $sale->id)->toArray())
            ->update([
                'status' => 'checkedout',
                'check_out_id' => $checkout->id
            ]);

        return response($checkout, 201);
    }

    public function getAgencyCheckouts(Request $request, AgencyInfo $agency)
    {
        return CheckoutResource::collection(CheckOut::where('agency_id', $agency->id)
            ->orderBy('created_at', 'desc')
            ->paginate($request->query('per_page', 10)));
    }

    public function getCheckOutsDetails(Request $request, Checkout $checkout)
    {
        $checkout->receipt = Storage::disk('public')->url($checkout->receipt);
        return response([
            'check_out' => $checkout,
            'sales' => TourReservationResource::collection($checkout->reservations),
        ]);
    }

    public function getMyCheckoutsForAgency(Request $request)
    {
        $user = $request->user()->agencyInfo;
        return CheckoutResource::collection($user->checkouts()->orderByDesc('created_at')->paginate(10));
    }

    public function getSaleCheckoutsForAgency(Request $request, CheckOut $checkout)
    {
        return TourReservationResource::collection($checkout->reservations()->orderByDesc('created_at')->paginate($request->query('per_page', 10)));
    }

    public function getNotMyCheckoutsForAgency(Request $request)
    {
        return TourReservationResource::collection(
            TourReservation::where('agency_id', $request->user()->agencyInfo->id)
                ->where('status', 'paid')
                ->orderBy('updated_at', 'desc')
                ->paginate(10)
        );
    }
}
