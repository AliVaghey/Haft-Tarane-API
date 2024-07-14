<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        'agency_id',
        'passengers',
        'passengers_count',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
//    protected function casts(): array
//    {
//        return [
//            'passengers' => AsCollection::class,
//        ];
//    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class, 'tour_id');
    }

    public function date(): BelongsTo
    {
        return $this->belongsTo(Date::class, 'date_id');
    }

    public function cost(): BelongsTo
    {
        return $this->belongsTo(Costs::class, 'cost_id');
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class, 'hotel_id');
    }

    public function files(): HasOne
    {
        return $this->hasOne(ReservationFile::class, 'reservation_id');
    }

    public function isPaid(): bool
    {
        return $this->status == 'paid' || $this->status == 'checkedout';
    }
}
