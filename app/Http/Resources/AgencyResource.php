<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * This resource receives an agency info model.
 */
class AgencyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $this->user();
        return [
            'id' => $user->id,
            'username' => $user->username,
            'user_phone' => $user->phone,
            'agency_name' => $this->name,
            'agency_address' => $this->address,
            'agecny_c_phone' => $this->c_phone,
            'agency_email' => $this->email,
            'agency_zip_code' => $this->zip_code,
            'agency_web_site' => $this->web_site,
        ];
    }
}
