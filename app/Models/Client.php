<?php

namespace App\Models;

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
        'client_type',
        'enabled',
        'revoked',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'revoked' => 'boolean',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    public function realm(): BelongsTo
    {
        return $this->belongsTo(Realm::class);
    }
}
