<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ads extends Model
{
    use HasFactory;

    protected $fillable = [
        'sort',
        'link',
        'description',
        'text_color',
        'background_color'
    ];
}
