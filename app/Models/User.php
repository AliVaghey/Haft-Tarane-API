<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserAccessType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'first_name_fa',
        'last_name_fa',
        'first_name_en',
        'last_name_en',
        'birth_date',
        'gender',
        'phone',
        'email',
        'password',
        'access_type',
        'national_code'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'access_type',
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'datetime:Y-m-d',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'access_type' => UserAccessType::class
        ];
    }

    /**
     * Determines whether the user is user or not.
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->access_type == UserAccessType::Admin;
    }

    /**
     * Determines whether the user is agency or not.
     * @return bool
     */
    public function isAgency(): bool
    {
        return $this->access_type === UserAccessType::Agency;
    }

    /**
     * Get the agency_info model.
     */
    public function agencyInfo(): HasOne
    {
        return $this->hasOne(AgencyInfo::class, 'user_id');
    }

    /**
     * Get the hotels of the admin user.
     */
    public function hotels(): HasMany
    {
        return $this->hasMany(Hotel::class, 'admin_id');
    }

    /**
     * Get the Support team of agency users.
     */
    public function supports(): HasMany
    {
        return $this->hasMany(Support::class, 'agency_id');
    }

    /**
     * Get the agencies of admin users.
     */
    public function agencies(): HasMany
    {
        return $this->hasMany(AgencyInfo::class, 'admin_id');
    }
}