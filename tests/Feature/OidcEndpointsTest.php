<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Realm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OidcEndpointsTest extends TestCase
{
    use RefreshDatabase;

    protected Realm $realm;
    protected User $user;
    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->realm = Realm::create([
            'name' => 'test-realm',
            'display_name' => 'Test Realm',
            'enabled' => true,
            'access_token_lifespan' => 300,
            'refresh_token_lifespan' => 1800,
        ]);

        $this->user = User::create([
            'realm_id' => $this->realm->id,
            'username' => 'testuser',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $this->client = Client::create([
            'id' => 'test-client',
            'realm_id' => $this->realm->id,
            'client_id' => 'test-client',
            'name' => 'Test Client',
            'secret' => 'test-secret',
            'redirect_uris' => json_encode(['http://localhost:3000/callback']),
            'grant_types' => json_encode(['authorization_code', 'refresh_token']),
            'enabled' => true,
            'revoked' => false,
        ]);
    }

    public function test_discovery_endpoint_returns_correct_structure(): void
    {
        $response = $this->get("/realms/{$this->realm->name}/.well-known/openid-configuration");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'issuer',
                'authorization_endpoint',
                'token_endpoint',
                'userinfo_endpoint',
                'end_session_endpoint',
                'jwks_uri',
                'response_types_supported',
                'subject_types_supported',
                'id_token_signing_alg_values_supported',
            ])
            ->assertJson([
                'response_types_supported' => ['code'],
                'subject_types_supported' => ['public'],
                'id_token_signing_alg_values_supported' => ['RS256'],
            ]);
    }

    public function test_jwks_endpoint_returns_public_keys(): void
    {
        $response = $this->get("/realms/{$this->realm->name}/protocol/openid-connect/certs");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'keys' => [
                    '*' => ['kid', 'kty', 'alg', 'use', 'n', 'e']
                ]
            ]);

        $data = $response->json();
        $this->assertEquals('RSA', $data['keys'][0]['kty']);
        $this->assertEquals('RS256', $data['keys'][0]['alg']);
    }

    public function test_authorization_endpoint_shows_login_page(): void
    {
        $params = [
            'response_type' => 'code',
            'client_id' => $this->client->client_id,  // Use OAuth client_id, not UUID
            'redirect_uri' => 'http://localhost:3000/callback',
            'scope' => 'openid profile email',
            'state' => 'test-state',
            'nonce' => 'test-nonce',
        ];
        
        $response = $this->get("/realms/{$this->realm->name}/protocol/openid-connect/auth?" . http_build_query($params));

        $response->assertStatus(200)
            ->assertSee('Sign in to your account')
            ->assertSee($this->client->name);
    }

    public function test_authorization_endpoint_validates_required_parameters(): void
    {
        $params = ['response_type' => 'code'];
        $response = $this->get("/realms/{$this->realm->name}/protocol/openid-connect/auth?" . http_build_query($params));

        $response->assertStatus(302);
    }

    public function test_authorization_endpoint_rejects_invalid_scope(): void
    {
        $params = [
            'response_type' => 'code',
            'client_id' => $this->client->client_id,  // Use OAuth client_id, not UUID
            'redirect_uri' => 'http://localhost:3000/callback',
            'scope' => 'profile email',
            'state' => 'test-state',
        ];
        
        $response = $this->get("/realms/{$this->realm->name}/protocol/openid-connect/auth?" . http_build_query($params));

        $response->assertRedirect();
        $this->assertStringContainsString('error=invalid_scope', $response->headers->get('Location'));
    }

    public function test_login_endpoint_authenticates_user_and_redirects_with_code(): void
    {
        session([
            'oidc_auth_request' => [
                'realm_id' => $this->realm->id,
                'client_id' => $this->client->id,  // Store UUID for lookup
                'oauth_client_id' => $this->client->client_id,  // Store OAuth client_id
                'redirect_uri' => 'http://localhost:3000/callback',
                'scope' => 'openid profile email',
                'state' => 'test-state',
                'nonce' => 'test-nonce',
            ]
        ]);

        $response = $this->post("/realms/{$this->realm->name}/protocol/openid-connect/auth/login", [
            'username' => 'testuser',
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $location = $response->headers->get('Location');
        
        $this->assertStringContainsString('http://localhost:3000/callback', $location);
        $this->assertStringContainsString('code=', $location);
        $this->assertStringContainsString('state=test-state', $location);
    }

    public function test_token_endpoint_exchanges_code_for_tokens(): void
    {
        $code = 'test-authorization-code';
        
        // Insert authorization code into database instead of session
        \DB::table('oauth_auth_codes')->insert([
            'id' => $code,
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,  // UUID
            'scopes' => json_encode(['openid', 'profile', 'email']),
            'redirect_uri' => 'http://localhost:3000/callback',
            'nonce' => 'test-nonce',
            'revoked' => false,
            'expires_at' => now()->addMinutes(5),
        ]);

        $response = $this->post("/realms/{$this->realm->name}/protocol/openid-connect/token", [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => 'http://localhost:3000/callback',
            'client_id' => $this->client->client_id,  // Use OAuth client_id
            'client_secret' => 'test-secret',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in',
                'refresh_token',
                'id_token',
                'scope',
            ])
            ->assertJson([
                'token_type' => 'bearer',
            ]);
    }

    public function test_userinfo_endpoint_requires_authentication(): void
    {
        $response = $this->get("/realms/{$this->realm->name}/protocol/openid-connect/userinfo");

        $response->assertStatus(401)
            ->assertJson(['error' => 'invalid_token']);
    }

    public function test_logout_endpoint_clears_session(): void
    {
        session(['oidc_realm_' . $this->realm->id => $this->user->id]);

        $params = ['post_logout_redirect_uri' => 'http://localhost:3000'];
        $response = $this->get("/realms/{$this->realm->name}/protocol/openid-connect/logout?" . http_build_query($params));

        $response->assertRedirect('http://localhost:3000');
        $this->assertNull(session('oidc_realm_' . $this->realm->id));
    }

    public function test_realm_must_exist_and_be_enabled(): void
    {
        $response = $this->get('/realms/nonexistent/.well-known/openid-configuration');
        $response->assertStatus(404);
    }
}
