<?php

namespace App\Enums;

//Updating any of the enums requires running migrations!

enum UserAccessType: string
{
    case User = 'user';
    case Agency = 'agency';
    case Admin = 'admin';
    case SuperAdmin = 'superadmin';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
