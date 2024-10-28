<?php

namespace App\Enums;

enum Currency: string
{
    case IRT = 'irt';
    case USD = 'usd';
    case EUR = 'eur';
    case AED = 'aed';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
