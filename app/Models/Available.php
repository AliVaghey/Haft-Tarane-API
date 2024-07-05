<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Available extends Model
{
    use HasFactory;

    protected $fillable = [
        'tour_id',
        'date_id',
        'cost_id',
        'min_cost',
        'expired'
    ];

    protected function casts(): array
    {
        return [
            'expired' => 'boolean',
        ];
    }

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class, 'tour_id');
    }

    public function date(): BelongsTo
    {
        return $this->belongsTo(Date::class, 'date_id');
    }

    public function  cost(): BelongsTo
    {
        return $this->belongsTo(Costs::class, 'cost_id');
    }
}
