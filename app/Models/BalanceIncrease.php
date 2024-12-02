<?php

namespace App\Models;

use App\Traits\Payable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BalanceIncrease extends Model
{
    use HasFactory, Payable;

    protected $fillable = [
        'user_id',
        'amount',
        'status',
        'transaction_id',
    ];

    protected function totalPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->amount,
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paid(...$params)
    {
        $this->update([
            'status' => 'paid',
            'transaction_id' => $params[0]
        ]);
        $this->user->appendBalance($this->amount);
    }

    public function paymentFailed(...$params)
    {
        $this->update([
            'status' => 'canceled'
        ]);
    }
}
