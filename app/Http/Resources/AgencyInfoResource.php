<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * This resource receives a user model which has agency info.
 */
class AgencyInfoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $info = $this->agencyInfo;
        return [
            'id' => $this->id,
            'username' => $this->username,
            'user_phone' => $this->phone,
            'agency_name' => $info ? $info->name : null,
            'agency_address' => $info ? $info->address : null,
            'agency_c_phone' => $info ? $info->c_phone : null,
            'agency_email' => $info ? $info->email : null,
            'agency_zip_code' => $info ? $info->zip_code : null,
            'agency_web_site' => $info ? $info->web_site : null,
        ];
    }
}
