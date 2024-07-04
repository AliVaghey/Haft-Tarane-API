<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CheckOut extends Model
{
    use HasFactory;

    protected $fillable = [
        'agency_id',
        'admin_id',
        'total_price',
        'profit',
        'real_price',
        'receipt',
        'description'
    ];

    public function agency(): BelongsTo
    {
        return $this->belongsTo(AgencyInfo::class, 'agency_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(TourReservation::class, 'check_out_id');
    }
}
