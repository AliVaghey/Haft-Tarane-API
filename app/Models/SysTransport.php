<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SysTransport extends Model
{
    use HasFactory;

    protected $fillable = [
        'flight_id',
        'tour_id',
        'returning',
        'date_id',
    ];

    public function delete()
    {
        if ($flight = FlightInfo::find($this->flight_id)) {
            $flight->delete();
        }

        return parent::delete();
    }

    public function flight(): BelongsTo
    {
        return $this->belongsTo(FlightInfo::class, 'flight_id');
    }

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class, 'tour_id');
    }

    public function getDate(): ?Date
    {
        return Date::find($this->date_id);
    }
}
