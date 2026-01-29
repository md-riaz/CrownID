<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Passport\Client as PassportClient;

class Client extends PassportClient
{
    protected $table = 'oauth_clients';

    protected $fillable = [
        'realm_id',
        'client_id',
        'name',
        'secret',
        'redirect_uris',
        'grant_types',
        'client_type',
        'enabled',
        'revoked',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'revoked' => 'boolean',
        'grant_types' => 'array',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    protected function redirectUris(): Attribute
    {
        return Attribute::make(
            get: function () {
                $value = $this->getAttributes()['redirect_uris'] ?? null;
                
                if (!empty($value)) {
                    return $this->fromJson($value);
                }
                
                return [];
            }
        );
    }

    protected function secret(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value,
            set: fn ($value) => $value,
        );
    }

    public function realm(): BelongsTo
    {
        return $this->belongsTo(Realm::class);
    }
}
