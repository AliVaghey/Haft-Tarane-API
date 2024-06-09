<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Airport extends Model
{
    use HasFactory;

    protected $fillable = [
        'IATA_code',
        'name_en',
        'name_fa',
    ];

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn() => config('app.locale') == 'fa' ? $this['name_fa'] : $this['name_en'],
        );
    }
}
