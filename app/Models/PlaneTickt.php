<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlaneTickt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'flight_info_id',
        'total_price',
        'passengers',
        'reservation_results',
        'buy_ticket_results',
        'status',
        'voucher',
    ];

    protected function casts()
    {
        return [
            'passengers' => 'array',
            'reservation_results' => 'array',
            'buy_ticket_results' => 'array',
        ];
    }
}
