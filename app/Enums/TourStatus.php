<?php

namespace App\Enums;

enum TourStatus: string
{
    case Active = 'active';
    case Draft = 'draft';
    case Canceled = 'canceled';
    case Expired = 'expired';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
