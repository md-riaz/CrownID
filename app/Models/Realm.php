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
        'mfa_enabled',
        'brute_force_protected',
        'max_login_attempts',
        'lockout_duration_minutes',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'access_token_lifespan' => 'integer',
        'refresh_token_lifespan' => 'integer',
        'sso_session_idle_timeout' => 'integer',
        'sso_session_max_lifespan' => 'integer',
        'mfa_enabled' => 'boolean',
        'brute_force_protected' => 'boolean',
        'max_login_attempts' => 'integer',
        'lockout_duration_minutes' => 'integer',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }
}
