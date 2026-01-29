<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'clientId' => $this->client_id ?? $this->id,
            'name' => $this->name,
            'enabled' => $this->enabled ?? true,
            'clientAuthenticatorType' => $this->client_type === 'confidential' ? 'client-secret' : 'none',
            'redirectUris' => $this->redirectUris ?? [],
            'secret' => $this->when($this->client_type === 'confidential', $this->secret),
            'publicClient' => $this->client_type === 'public',
            'protocol' => 'openid-connect',
        ];
    }
}
