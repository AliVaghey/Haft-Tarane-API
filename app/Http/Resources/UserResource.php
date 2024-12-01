<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'national_code' => $this->national_code,
            'phone' => $this->phone,
            'username' => $this->username,
            'gender' => $this->gender,
            'first_name_fa' => $this->first_name_fa,
            'last_name_fa' => $this->last_name_fa,
            'first_name_en' => $this->first_name_en,
            'last_name_en' => $this->last_name_en,
            'email' => $this->email,
            'access_type' => $this->access_type,
            'balance' => $this->balance,
        ];
    }
}
