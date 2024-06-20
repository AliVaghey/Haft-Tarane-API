<?php

namespace App\Enums;

enum ReservationStatus: string
{


    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
