<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditEvent extends Model
{
    protected $fillable = [
        'event_type',
        'realm_id',
        'user_id',
        'ip_address',
        'details',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    public function realm(): BelongsTo
    {
        return $this->belongsTo(Realm::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
