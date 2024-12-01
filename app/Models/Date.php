<?php

namespace App\Models;

use App\Enums\UserAccessType;
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
        'expired',
        'price_change',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expired' => 'boolean'
        ];
    }

    public function delete()
    {
        $availables = Available::where('date_id', $this->id)->get();
        foreach ($availables as $available) {
            $available->delete();
        }
        $price_changes = PriceChange::where('date_id', $this->id)->get();
        foreach ($price_changes as $price) {
            $price->delete();
        }
        return parent::delete();
    }

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
