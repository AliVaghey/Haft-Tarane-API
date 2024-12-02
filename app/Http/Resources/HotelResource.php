<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class HotelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if ($this->gallery) {
            $gallery = $this->gallery->map(function ($path) {
                return Storage::disk('public')->url($path);
            });
        }
        return [
            'id' => $this->id,
            'author' => $this->when($request->user()->isAdmin(), $this->admin->username),
            'name' => $this->name,
            'address' => $this->address,
            'country' => $this->country,
            'state' => $this->state,
            'city' => $this->city,
            'gallery' => $gallery ?? [],
            'created_at' => $this->created_at,
            'stars' => $this->stars,
        ];
    }
}
