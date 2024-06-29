<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationFile extends Model
{
    use HasFactory;

    protected $fillable = [
       'reservation_id',
       'files'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'files' => AsCollection::class,
        ];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(TourReservation::class, 'reservation_id');
    }
}
