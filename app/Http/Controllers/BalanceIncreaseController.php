<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BalanceIncreaseController extends Controller
{
    public function getAll(Request $request)
    {
        return $request->user()->balanceIncrease()->latest()->paginate();
    }
}
