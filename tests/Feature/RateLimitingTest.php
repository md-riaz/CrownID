<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Realm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    protected Realm $realm;
    protected Client $client;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->realm = Realm::create([
            'name' => 'test-realm',
            'display_name' => 'Test Realm',
            'enabled' => true,
            'brute_force_protected' => true,
            'max_login_attempts' => 3,
            'lockout_duration_minutes' => 30,
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

    public function test_account_locks_after_max_attempts()
    {
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

        for ($i = 0; $i < 3; $i++) {
            $response = $this->post("/realms/{$this->realm->name}/protocol/openid-connect/auth/login", [
                'username' => 'testuser',
                'password' => 'wrong-password',
            ]);
            $this->user->refresh();
        }

        $this->assertEquals(3, $this->user->failed_login_attempts);
        $this->user->refresh();
        $this->assertNotNull($this->user->account_locked_until, 'Account should be locked after 3 failed attempts');
        $this->assertTrue($this->user->isAccountLocked(), 'isAccountLocked should return true');
    }

    public function test_locked_account_cannot_login()
    {
        $this->user->lockAccount(30);

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

        $response->assertSessionHasErrors(['username']);
        $this->assertStringContainsString('locked', $response->getSession()->get('errors')->first('username'));
    }

    public function test_account_unlocks_after_duration()
    {
        $this->user->account_locked_until = now()->subMinutes(1);
        $this->user->save();

        $isLocked = $this->user->isAccountLocked();

        $this->assertFalse($isLocked);
        $this->user->refresh();
        $this->assertNull($this->user->account_locked_until);
        $this->assertEquals(0, $this->user->failed_login_attempts);
    }

    public function test_successful_login_resets_attempts()
    {
        $this->user->failed_login_attempts = 2;
        $this->user->save();

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

        $this->user->refresh();
        $this->assertEquals(0, $this->user->failed_login_attempts);
        $this->assertNull($this->user->account_locked_until);
    }

    public function test_brute_force_protection_can_be_disabled()
    {
        $this->realm->brute_force_protected = false;
        $this->realm->save();

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

        for ($i = 0; $i < 5; $i++) {
            $this->post("/realms/{$this->realm->name}/protocol/openid-connect/auth/login", [
                'username' => 'testuser',
                'password' => 'wrong-password',
            ]);
        }

        $this->user->refresh();
        $this->assertFalse($this->user->isAccountLocked());
    }

    public function test_login_attempts_are_recorded()
    {
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

        $this->post("/realms/{$this->realm->name}/protocol/openid-connect/auth/login", [
            'username' => 'testuser',
            'password' => 'wrong-password',
        ]);

        $this->assertDatabaseHas('login_attempts', [
            'user_id' => $this->user->id,
            'successful' => false,
        ]);

        $this->post("/realms/{$this->realm->name}/protocol/openid-connect/auth/login", [
            'username' => 'testuser',
            'password' => 'password',
        ]);

        $this->assertDatabaseHas('login_attempts', [
            'user_id' => $this->user->id,
            'successful' => true,
        ]);
    }
}
