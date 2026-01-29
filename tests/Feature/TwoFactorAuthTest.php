<?php

namespace Tests\Feature;

use App\Models\BackupCode;
use App\Models\Client;
use App\Models\Realm;
use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorAuthTest extends TestCase
{
    use RefreshDatabase;

    protected TwoFactorService $twoFactorService;
    protected Realm $realm;
    protected Client $client;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->twoFactorService = new TwoFactorService();
        
        $this->realm = Realm::create([
            'name' => 'test-realm',
            'display_name' => 'Test Realm',
            'enabled' => true,
            'mfa_enabled' => true,
        ]);

        $this->client = Client::create([
            'id' => 'test-client',
            'realm_id' => $this->realm->id,
            'client_id' => 'test-client-id',
            'name' => 'Test Client',
            'secret' => 'test-secret',
            'redirect_uris' => json_encode(['http://localhost/callback']),
            'enabled' => true,
            'grant_types' => json_encode(['authorization_code']),
            'revoked' => false,
        ]);

        $this->user = User::create([
            'realm_id' => $this->realm->id,
            'username' => 'testuser',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);
    }

    public function test_totp_setup_generates_secret()
    {
        $secret = $this->user->generateTwoFactorSecret();
        
        $this->assertNotNull($secret);
        $this->assertGreaterThanOrEqual(16, strlen($secret));
        $this->user->refresh();
        $this->assertNotNull($this->user->two_factor_secret);
    }

    public function test_totp_verification_succeeds_with_valid_code()
    {
        $secret = $this->user->generateTwoFactorSecret();
        $this->user->two_factor_enabled = true;
        $this->user->save();
        
        $google2fa = new Google2FA();
        $validCode = $google2fa->getCurrentOtp($secret);
        
        $result = $this->user->verifyTwoFactorCode($validCode);
        
        $this->assertTrue($result);
    }

    public function test_totp_verification_fails_with_invalid_code()
    {
        $secret = $this->user->generateTwoFactorSecret();
        $this->user->two_factor_enabled = true;
        $this->user->save();
        
        $result = $this->user->verifyTwoFactorCode('000000');
        
        $this->assertFalse($result);
    }

    public function test_backup_codes_generation()
    {
        $codes = $this->twoFactorService->generateBackupCodes($this->user);
        
        $this->assertCount(8, $codes);
        $this->assertEquals(8, $this->user->backupCodes()->count());
        
        foreach ($codes as $code) {
            $this->assertEquals(8, strlen($code));
        }
    }

    public function test_backup_code_verification_works()
    {
        $codes = $this->twoFactorService->generateBackupCodes($this->user);
        $testCode = $codes[0];
        
        $result = $this->twoFactorService->verifyBackupCode($this->user, $testCode);
        
        $this->assertTrue($result);
        
        $backupCode = $this->user->backupCodes()->first();
        $this->assertNotNull($backupCode->used_at);
    }

    public function test_backup_code_cannot_be_reused()
    {
        $codes = $this->twoFactorService->generateBackupCodes($this->user);
        $testCode = $codes[0];
        
        $this->twoFactorService->verifyBackupCode($this->user, $testCode);
        $result = $this->twoFactorService->verifyBackupCode($this->user, $testCode);
        
        $this->assertFalse($result);
    }

    public function test_mfa_required_in_authorization_flow()
    {
        $secret = $this->user->generateTwoFactorSecret();
        $this->user->two_factor_enabled = true;
        $this->user->save();

        $response = $this->post("/realms/{$this->realm->name}/protocol/openid-connect/auth/login", [
            'username' => 'testuser',
            'password' => 'password',
        ], [
            'Referer' => url("/realms/{$this->realm->name}/protocol/openid-connect/auth")
        ]);

        session([
            'oidc_auth_request' => [
                'realm_id' => $this->realm->id,
                'client_id' => $this->client->id,
                'redirect_uri' => 'http://localhost/callback',
                'scope' => 'openid',
                'state' => 'test-state',
                'nonce' => 'test-nonce',
            ]
        ]);

        $response = $this->post("/realms/{$this->realm->name}/protocol/openid-connect/auth/login", [
            'username' => 'testuser',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('oidc.mfa-challenge', ['realm' => $this->realm->name]));
    }

    public function test_mfa_verification_completes_login()
    {
        $secret = $this->user->generateTwoFactorSecret();
        $this->user->two_factor_enabled = true;
        $this->user->save();

        session([
            'oidc_mfa_user_' . $this->realm->id => $this->user->id,
            'oidc_auth_request' => [
                'realm_id' => $this->realm->id,
                'client_id' => $this->client->id,
                'redirect_uri' => 'http://localhost/callback',
                'scope' => 'openid',
                'state' => 'test-state',
                'nonce' => 'test-nonce',
            ]
        ]);

        $google2fa = new Google2FA();
        $validCode = $google2fa->getCurrentOtp($secret);

        $response = $this->post("/realms/{$this->realm->name}/protocol/openid-connect/mfa", [
            'code' => $validCode,
        ]);

        $response->assertRedirect();
        $this->assertStringContainsString('code=', $response->headers->get('Location'));
    }

    public function test_qr_code_url_generation()
    {
        $secret = $this->twoFactorService->generateSecret();
        $url = $this->twoFactorService->generateQrCodeUrl($secret, 'test@example.com');
        
        $this->assertStringContainsString('otpauth://totp/', $url);
        $this->assertStringContainsString(urlencode('test@example.com'), $url);
        $this->assertStringContainsString($secret, $url);
    }
}
