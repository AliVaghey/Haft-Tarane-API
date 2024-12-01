<?php

namespace App\Models;

use App\Traits\Payable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlaneTickt extends Model
{
    use HasFactory, Payable;

    protected $fillable = [
        'user_id',
        'transaction_id',
        'flight_info_id',
        'total_price',
        'passengers',
        'reservation_results',
        'buy_ticket_results',
        'descriptions',
        'status',
        'voucher',
    ];

    protected function casts()
    {
        return [
            'passengers' => 'array',
            'reservation_results' => 'array',
            'buy_ticket_results' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notPayable(): bool
    {
        if ($this->status != 'pending') {
            return true;
        }
        return false;
    }

    public function paid(...$params)
    {
        try {
            $results = air_service()->buyTicket($this->voucher);
            $this->update([
                'buy_ticket_results' => $results,
                'status' => 'paid',
                'transaction_id' => $params[0],
                'descriptions' => "بیلط با موفقیت صادر شد.",
            ]);
        } catch (\Exception $exception) {
            $description = "مشکلی برای صدور بلیط رخ داده و هزینه آن به کیف پول انتقال داده شد است. لطفا با پشتیبانی سایت تماس بگیرید.";
            $this->update([
                'buy_ticket_results' => $results,
                'status' => 'paid',
                'transaction_id' => $params[0],
                'descriptions' => $description,
            ]);
            $this->user->appendBalance($this->total_price);
        }


            $this->update([
                'status' => 'paid',
                'transaction_id' => $params[0],
                'descriptions' => "بیلط با موفقیت صادر شد.",
            ]);
    }

    public function paymentFailed(...$params)
    {
        $this->update([
            'transaction_id' => $params[0],
            'status' => 'canceled'
        ]);
    }
}
