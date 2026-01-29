<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

class BackupCode extends Model
{
    protected $fillable = [
        'user_id',
        'code',
        'used_at',
    ];

    protected $casts = [
        'used_at' => 'datetime',
    ];

    protected $hidden = [
        'code',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }

    public function markAsUsed(): void
    {
        $this->used_at = now();
        $this->save();
    }

    public function verify(string $code): bool
    {
        return Hash::check($code, $this->code);
    }
}
