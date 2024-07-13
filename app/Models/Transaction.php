<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'transaction_id',
        'status',
        'reference_id',
        'object_id',
        'type'
    ];

    public function getObject()
    {
        return match ($this->type) {
            'tour_reservation' => TourReservation::find($this->object_id),
            default => null
        };
    }
}
