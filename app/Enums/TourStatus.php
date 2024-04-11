<?php

namespace App\Enums;

enum TourStatus: string
{
    case Active = 'active';
    case Draft = 'draft';
    case Pending = 'pending';
    case Rejected = 'rejected';
    case Canceled = 'canceled';
    case Expired = 'expired';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
