<?php

namespace App\Services;

use App\Models\LoginAttempt;
use App\Models\Realm;
use App\Models\User;
use Illuminate\Http\Request;

class RateLimitService
{
    public function __construct(
        protected AuditService $auditService
    ) {}

    public function recordLoginAttempt(User $user, string $ipAddress, bool $successful): void
    {
        LoginAttempt::create([
            'user_id' => $user->id,
            'ip_address' => $ipAddress,
            'successful' => $successful,
            'attempted_at' => now(),
        ]);

        if (!$successful) {
            $user->increment('failed_login_attempts');
        } else {
            $user->resetFailedLoginAttempts();
        }
    }

    public function checkAndLockIfNeeded(User $user): void
    {
        $user->refresh();
        $user->load('realm');
        
        $realm = $user->realm;
        
        if (!$realm || !$realm->brute_force_protected) {
            return;
        }

        if ($user->failed_login_attempts >= $realm->max_login_attempts) {
            $user->lockAccount($realm->lockout_duration_minutes);
            $this->auditService->logAccountLocked($user->id, $realm->id);
        }
    }

    public function isRateLimited(User $user): bool
    {
        return $user->isAccountLocked();
    }

    public function resetAttempts(User $user): void
    {
        $user->resetFailedLoginAttempts();
    }

    protected function checkAndLockAccount(User $user): void
    {
        // This method is no longer used directly
    }
}
