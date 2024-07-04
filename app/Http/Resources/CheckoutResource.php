<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CheckoutResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'agency' => [
                'id' => $this->agency->id,
                'name' => $this->agency->name,
            ],
            'admin' => [
                'id' => $this->admin->id,
                'name' => $this->admin->username,
            ],
            'total_price' => $this->total_price,
            'profit' => $this->profit,
            'description' => $this->description,
            'receipt' => Storage::disk('public')->url($this->receipt),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
