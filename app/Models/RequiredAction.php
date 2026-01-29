<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequiredAction extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'required',
        'completed_at',
    ];

    protected $casts = [
        'required' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    public function complete(): void
    {
        $this->completed_at = now();
        $this->save();
    }
}
