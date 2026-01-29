<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Realm extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'enabled',
        'access_token_lifespan',
        'refresh_token_lifespan',
        'sso_session_idle_timeout',
        'sso_session_max_lifespan',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'access_token_lifespan' => 'integer',
        'refresh_token_lifespan' => 'integer',
        'sso_session_idle_timeout' => 'integer',
        'sso_session_max_lifespan' => 'integer',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }
}
