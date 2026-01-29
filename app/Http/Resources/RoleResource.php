<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'composite' => $this->composite,
            'clientRole' => $this->isClientRole(),
            'containerId' => $this->client_id ? (string) $this->client_id : (string) $this->realm_id,
        ];
    }
}
