<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserAccessType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
        'name',
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
}
