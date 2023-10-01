<?php

namespace App\Http\Resources\User;

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
        $profile = $this->profile;

        return [
            'id' => $this->id,
            'email' => $this->email,
            'last_name' => $profile->last_name,
            'first_name' => $profile->first_name,
            'middle_name' => $profile->middle_name,
            'phone' => $profile->phone,
            'avatar' => $profile->avatar
        ];
    }
}
