<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'uniqueID',
        'type',
        'price_final',
        'price_final_chd',
        'price_final_inf',
        'price_final_fare',
        'price_final_chd_fare',
        'price_final_inf_fare',
        'capacity',
        'from',
        'to',
        'number_flight',
        'type_flight',
        'carrier',
        'date_flight',
        'time_flight',
        'airline',
        'IATA_code',
        'cabinclass',
        'SellingType',
        'weelchairsupport',
        'price_Markup',
        'Share_Sale',
        'has_stop',
        'alarm_msg',
    ];
}
