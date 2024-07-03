<?php

namespace App\Http\Controllers;

use App\Http\Resources\AgenciesCheckOutResource;
use Illuminate\Http\Request;

class CheckOutController extends Controller
{
    public function getAgencies(Request $request)
    {
        return AgenciesCheckOutResource::collection($request->user()->agencies()->paginate(10));
    }
}
