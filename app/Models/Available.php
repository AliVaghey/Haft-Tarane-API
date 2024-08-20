<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Available extends Model
{
    use HasFactory, MassPrunable;

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

    /**
     * Get the prunable model query.
     */
    public function prunable(): Builder
    {
        return static::where('expired', true)->where('updated_at', '<=', now()->subMonth());
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
}
