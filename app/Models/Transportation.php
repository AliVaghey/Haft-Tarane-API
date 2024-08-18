<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transportation extends Model
{
    use HasFactory;

    protected $fillable = [
        'tour_id',
        'sort',
        'type',
        'origin',
        'destination',
        'start',
        'end',
        'duration',
        'company_name',
        'transportation_type',
        'price',
    ];

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }
}
