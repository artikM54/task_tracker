<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MinimalInfoUserResource extends JsonResource
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
            'email' => $this->email,
            'full_name' => $this->getFullName()
        ];
    }

    protected function getFullName(): string
    {
        $profile = $this->profile;

        $fullNameUser = "{$profile->last_name} {$profile->first_name} {$profile->middle_name}";
        $fullNameUser = preg_replace('/\s+/', ' ', trim($fullNameUser));

        return $fullNameUser;
    }
}
