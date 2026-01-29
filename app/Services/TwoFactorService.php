<?php

namespace App\Services;

use App\Models\BackupCode;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorService
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    public function generateQrCodeUrl(string $secret, string $email): string
    {
        $appName = config('app.name', 'CrownID');
        return $this->google2fa->getQRCodeUrl($appName, $email, $secret);
    }

    public function verify(string $secret, string $code): bool
    {
        return $this->google2fa->verifyKey($secret, $code);
    }

    public function generateBackupCodes(User $user, int $count = 8): array
    {
        $user->backupCodes()->delete();
        
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $code = strtoupper(substr(bin2hex(random_bytes(5)), 0, 8));
            $codes[] = $code;
            
            BackupCode::create([
                'user_id' => $user->id,
                'code' => Hash::make($code),
            ]);
        }
        
        return $codes;
    }

    public function verifyBackupCode(User $user, string $code): bool
    {
        $backupCodes = $user->backupCodes()->whereNull('used_at')->get();
        
        foreach ($backupCodes as $backupCode) {
            if ($backupCode->verify($code)) {
                $backupCode->markAsUsed();
                return true;
            }
        }
        
        return false;
    }
}
