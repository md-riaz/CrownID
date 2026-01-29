<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RealmResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'realm' => $this->name,
            'displayName' => $this->display_name,
            'enabled' => $this->enabled,
            'accessTokenLifespan' => $this->access_token_lifespan,
            'refreshTokenLifespan' => $this->refresh_token_lifespan,
            'ssoSessionIdleTimeout' => $this->sso_session_idle_timeout,
            'ssoSessionMaxLifespan' => $this->sso_session_max_lifespan,
        ];
    }
}
