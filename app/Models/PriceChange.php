<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use stdClass;

class PriceChange extends Model
{
    use HasFactory;

    protected $fillable = [
        'date_id',
        'cost_id',
        'price_change',
        'one_bed',
        'two_bed',
        'plus_one',
        'cld_6',
        'cld_2',
        'baby',
        'currency',
    ];

    public function toCurrency(): stdClass
    {
        if ($this->currency == 'irt') {
            $unit = 1;
        } else {
            $unit = Options::firstWhere('category', $this->currency . "-currency-unit")->value;
        }
        return (object)[
            'one_bed' => $this->one_bed * $unit,
            'two_bed' => $this->two_bed * $unit,
            'plus_one' => $this->plus_one * $unit,
            'cld_6' => $this->cld_6 * $unit,
            'cld_2' => $this->cld_2 * $unit,
            'baby' => $this->baby * $unit,
        ];
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
