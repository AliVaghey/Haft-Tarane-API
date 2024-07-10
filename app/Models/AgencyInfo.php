<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AgencyInfo extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'admin_id',
        'name',
        'address',
        'c_phone',
        'email',
        'zip_code',
        'web_site',
        'description',
        'instagram',
        'telegram',
        'whatsapp'
    ];

    /**
     * Get the user model.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the agency tours.
     */
    public function tours(): HasMany
    {
        return $this->hasMany(Tour::class, 'agency_id');
    }

    /**
     * Get the admin model of the agency.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function checkouts(): HasMany
    {
        return $this->hasMany(Checkout::class, 'agency_id');
    }
}
