<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $names = $this->parseName();
        
        return [
            'id' => (string) $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'firstName' => $names['firstName'],
            'lastName' => $names['lastName'],
            'enabled' => true,
            'emailVerified' => $this->email_verified_at !== null,
            'attributes' => $this->attributes ?? [],
            'createdTimestamp' => $this->created_at?->getTimestamp() * 1000,
        ];
    }

    private function parseName(): array
    {
        $name = $this->name ?? '';
        $parts = explode(' ', $name, 2);
        
        return [
            'firstName' => $parts[0] ?? '',
            'lastName' => $parts[1] ?? '',
        ];
    }
}
