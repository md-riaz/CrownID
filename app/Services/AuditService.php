<?php

namespace App\Services;

use App\Models\AuditEvent;
use Illuminate\Http\Request;

class AuditService
{
    public function log(
        string $eventType,
        ?int $realmId = null,
        ?int $userId = null,
        array $details = [],
        ?Request $request = null
    ): AuditEvent {
        $ipAddress = null;
        
        if ($request) {
            $ipAddress = $request->ip();
        } elseif (request()) {
            $ipAddress = request()->ip();
        }

        return AuditEvent::create([
            'event_type' => $eventType,
            'realm_id' => $realmId,
            'user_id' => $userId,
            'ip_address' => $ipAddress,
            'details' => $details,
        ]);
    }

    public function logLoginSuccess(int $userId, int $realmId, ?Request $request = null): void
    {
        $this->log('LOGIN_SUCCESS', $realmId, $userId, [], $request);
    }

    public function logLoginFailed(int $realmId, array $details = [], ?Request $request = null): void
    {
        $this->log('LOGIN_FAILED', $realmId, null, $details, $request);
    }

    public function logLogout(int $userId, int $realmId, ?Request $request = null): void
    {
        $this->log('LOGOUT', $realmId, $userId, [], $request);
    }

    public function logMfaEnabled(int $userId, int $realmId, ?Request $request = null): void
    {
        $this->log('MFA_ENABLED', $realmId, $userId, [], $request);
    }

    public function logMfaDisabled(int $userId, int $realmId, ?Request $request = null): void
    {
        $this->log('MFA_DISABLED', $realmId, $userId, [], $request);
    }

    public function logPasswordChanged(int $userId, int $realmId, ?Request $request = null): void
    {
        $this->log('PASSWORD_CHANGED', $realmId, $userId, [], $request);
    }

    public function logAccountLocked(int $userId, int $realmId, ?Request $request = null): void
    {
        $this->log('ACCOUNT_LOCKED', $realmId, $userId, [], $request);
    }
}
