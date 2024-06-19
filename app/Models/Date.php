<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Date extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tour_id',
        'start',
        'end',
    ];

    /**
     * Get the tour model.
     */
    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    public function sysTransportations(): HasMany
    {
        return $this->hasMany(SysTransport::class, 'date_id');
    }
}
