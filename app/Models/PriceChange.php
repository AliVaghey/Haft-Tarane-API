<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceChange extends Model
{
    use HasFactory;

    protected $fillable = [
        'date_id',
        'cost_id',
        'price_change',
    ];

    public function date(): BelongsTo
    {
        return $this->belongsTo(Date::class, 'date_id');
    }

    public function cost(): BelongsTo
    {
        return $this->belongsTo(Costs::class, 'cost_id');
    }
}
