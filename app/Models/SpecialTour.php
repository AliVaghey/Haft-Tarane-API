<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class SpecialTour extends Model
{
    use HasFactory;

    protected $fillable = [
        'photo',
        'tour_id',
        'importance',
        'advertisement',
    ];

    public function removePhoto()
    {
        Storage::disk('public')->delete($this->photo);
    }

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }
}
