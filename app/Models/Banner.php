<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'photo',
        'description',
        'link'
    ];

    public function delete()
    {
        Storage::disk('public')->delete($this->photo);
        return parent::delete();
    }
}
