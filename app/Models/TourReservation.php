<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourReservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tour_id',
        'date_id',
        'cost_id',
        'hotel_id',
        'total_price',
        'passengers',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'passengers' => AsCollection::class,
    ];
}
