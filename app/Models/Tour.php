<?php

namespace App\Models;

use App\Enums\TourStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tour extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'agency_id',
        'title',
        'support_id',
        'trip_type',
        'capacity',
        'expiration',
        'selling_type',
        'tour_styles',
        'evening_support',
        'midnight_support',
        'transportation_id',
        'origin',
        'destination',
        'staying_nights',
        'transportation_type',
        'status',
        'profit_rate',
        'labels',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TourStatus::class,
            'tour_styles' => AsCollection::class,
            'hotels' => AsCollection::class,
            'labels' => AsCollection::class,
            'evening_support' => 'boolean',
            'midnight_support' => 'boolean',
        ];
    }

    public function isActive(): bool
    {
        return $this->status == TourStatus::Active;
    }

    /**
     * Get the agencyInfo model.
     */
    public function agency(): BelongsTo
    {
        return $this->belongsTo(AgencyInfo::class, 'agency_id');
    }

    /**
     * Get the certificate model.
     */
    public function certificate(): HasOne
    {
        return $this->hasOne(certificate::class, 'tour_id');
    }

    /**
     * Get the rejection models.
     */
    public function rejections(): HasMany
    {
        return $this->hasMany(Rejection::class, 'tour_id');
    }

    /**
     * Get all the date models.
     */
    public function dates(): HasMany
    {
        return $this->hasMany(Date::class, 'tour_id');
    }

    /**
     * Get all the costs.
     */
    public function costs(): HasMany
    {
        return $this->hasMany(Costs::class, 'tour_id');
    }

    public function transportations(): HasMany
    {
        return $this->hasMany(Transportation::class, 'tour_id');
    }

    public function sysTransport(): HasMany
    {
        return $this->hasMany(SysTransport::class, 'tour_id');
    }

    public function isSysTrans(): bool
    {
        return $this->transportation_type == "system";
    }

    public function support(): BelongsTo
    {
        return $this->belongsTo(Support::class, 'support_id');
    }
}
