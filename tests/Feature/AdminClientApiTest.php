<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Realm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminClientApiTest extends TestCase
{
    use RefreshDatabase;

    protected Realm $realm;

    protected function setUp(): void
    {
        parent::setUp();

        $this->realm = Realm::create([
            'name' => 'test-realm',
            'display_name' => 'Test Realm',
            'enabled' => true,
        ]);
    }

    public function test_can_list_clients_in_realm(): void
    {
        Client::create([
            'id' => 'client-1',
            'realm_id' => $this->realm->id,
            'client_id' => 'client-1',
            'name' => 'Client One',
            'secret' => 'secret1',
            'redirect_uris' => json_encode(['http://localhost:3000/callback']),
            'grant_types' => 'authorization_code',
            'personal_access_client' => false,
            'password_client' => false,
            'revoked' => false,
            'client_type' => 'confidential',
            'enabled' => true,
        ]);

        Client::create([
            'id' => 'client-2',
            'realm_id' => $this->realm->id,
            'client_id' => 'client-2',
            'name' => 'Client Two',
            'secret' => null,
            'redirect_uris' => json_encode(['http://localhost:3000/callback']),
            'grant_types' => 'authorization_code',
            'personal_access_client' => false,
            'password_client' => false,
            'revoked' => false,
            'client_type' => 'public',
            'enabled' => true,
        ]);

        $response = $this->getJson("/api/admin/realms/{$this->realm->name}/clients");

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'clientId',
                    'name',
                    'enabled',
                    'clientAuthenticatorType',
                    'redirectUris',
                    'publicClient',
                ]
            ]);
    }

    public function test_can_filter_clients_by_client_id(): void
    {
        Client::create([
            'id' => 'client-1',
            'realm_id' => $this->realm->id,
            'client_id' => 'specific-client',
            'name' => 'Specific Client',
            'secret' => 'secret1',
            'redirect_uris' => json_encode(['http://localhost:3000/callback']),
            'grant_types' => 'authorization_code',

            'personal_access_client' => false,
            'password_client' => false,
            'revoked' => false,
            'client_type' => 'confidential',
            'enabled' => true,
        ]);

        Client::create([
            'id' => 'client-2',
            'realm_id' => $this->realm->id,
            'client_id' => 'other-client',
            'name' => 'Other Client',
            'secret' => 'secret2',
            'redirect_uris' => json_encode(['http://localhost:3000/callback']),
            'grant_types' => 'authorization_code',

            'personal_access_client' => false,
            'password_client' => false,
            'revoked' => false,
            'client_type' => 'confidential',
            'enabled' => true,
        ]);

        $response = $this->getJson("/api/admin/realms/{$this->realm->name}/clients?clientId=specific-client");

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonPath('0.clientId', 'specific-client');
    }

    public function test_pagination_works_for_client_list(): void
    {
        for ($i = 1; $i <= 15; $i++) {
            Client::create([
                'id' => "client-{$i}",
                'realm_id' => $this->realm->id,
                'client_id' => "client-{$i}",
                'name' => "Client {$i}",
                'secret' => "secret{$i}",
                'personal_access_client' => false,
                'password_client' => false,
                'revoked' => false,
                'client_type' => 'confidential',
                'enabled' => true,
                'redirect_uris' => json_encode(['http://localhost:3000/callback']),
                'grant_types' => json_encode(['authorization_code']),
            ]);
        }

        $response = $this->getJson("/api/admin/realms/{$this->realm->name}/clients?first=0&max=5");
        $response->assertStatus(200)->assertJsonCount(5);

        $response = $this->getJson("/api/admin/realms/{$this->realm->name}/clients?first=5&max=5");
        $response->assertStatus(200)->assertJsonCount(5);
    }

    public function test_can_get_client_by_id(): void
    {
        $client = Client::create([
            'id' => 'test-client',
            'realm_id' => $this->realm->id,
            'client_id' => 'test-client-id',
            'name' => 'Test Client',
            'secret' => 'test-secret',
            'redirect_uris' => json_encode(['http://localhost:3000/callback']),
            'grant_types' => json_encode(['authorization_code']),
            'personal_access_client' => false,
            'password_client' => false,
            'revoked' => false,
            'client_type' => 'confidential',
            'enabled' => true,
        ]);

        $response = $this->getJson("/api/admin/realms/{$this->realm->name}/clients/{$client->id}");

        $response->assertStatus(200)
            ->assertJson([
                'clientId' => 'test-client-id',
                'name' => 'Test Client',
                'enabled' => true,
                'publicClient' => false,
                'clientAuthenticatorType' => 'client-secret',
            ]);
    }

    public function test_returns_404_for_nonexistent_client(): void
    {
        $response = $this->getJson("/api/admin/realms/{$this->realm->name}/clients/nonexistent");
        $response->assertStatus(404);
    }

    public function test_can_create_confidential_client(): void
    {
        $data = [
            'clientId' => 'new-confidential-client',
            'name' => 'New Confidential Client',
            'secret' => 'my-custom-secret',
            'enabled' => true,
            'publicClient' => false,
            'redirectUris' => [
                'http://localhost:3000/callback',
                'http://localhost:3000/silent-callback',
            ],
        ];

        $response = $this->postJson("/api/admin/realms/{$this->realm->name}/clients", $data);

        $response->assertStatus(201)
            ->assertJson([
                'clientId' => 'new-confidential-client',
                'name' => 'New Confidential Client',
                'enabled' => true,
                'publicClient' => false,
                'clientAuthenticatorType' => 'client-secret',
            ]);

        $this->assertDatabaseHas('oauth_clients', [
            'realm_id' => $this->realm->id,
            'client_id' => 'new-confidential-client',
            'client_type' => 'confidential',
        ]);
    }

    public function test_can_create_public_client(): void
    {
        $data = [
            'clientId' => 'new-public-client',
            'name' => 'New Public Client',
            'enabled' => true,
            'publicClient' => true,
            'redirectUris' => ['http://localhost:3000/callback'],
        ];

        $response = $this->postJson("/api/admin/realms/{$this->realm->name}/clients", $data);

        $response->assertStatus(201)
            ->assertJson([
                'clientId' => 'new-public-client',
                'name' => 'New Public Client',
                'enabled' => true,
                'publicClient' => true,
                'clientAuthenticatorType' => 'none',
            ])
            ->assertJsonMissing(['secret']);

        $this->assertDatabaseHas('oauth_clients', [
            'realm_id' => $this->realm->id,
            'client_id' => 'new-public-client',
            'client_type' => 'public',
        ]);
    }

    public function test_confidential_client_auto_generates_secret_if_not_provided(): void
    {
        $data = [
            'clientId' => 'auto-secret-client',
            'name' => 'Auto Secret Client',
            'publicClient' => false,
        ];

        $response = $this->postJson("/api/admin/realms/{$this->realm->name}/clients", $data);

        $response->assertStatus(201)
            ->assertJsonStructure(['secret']);

        $secret = $response->json('secret');
        $this->assertNotEmpty($secret);
        $this->assertGreaterThan(20, strlen($secret));
    }

    public function test_can_update_client(): void
    {
        $client = Client::create([
            'id' => 'update-client',
            'realm_id' => $this->realm->id,
            'client_id' => 'update-client',
            'name' => 'Original Name',
            'secret' => 'original-secret',
            'redirect_uris' => json_encode(['http://localhost:3000/callback']),
            'grant_types' => json_encode(['authorization_code']),
            'personal_access_client' => false,
            'password_client' => false,
            'revoked' => false,
            'client_type' => 'confidential',
            'enabled' => true,
        ]);

        $response = $this->putJson("/api/admin/realms/{$this->realm->name}/clients/{$client->id}", [
            'name' => 'Updated Name',
            'redirectUris' => [
                'http://localhost:4000/callback',
                'http://localhost:4000/silent-callback',
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'name' => 'Updated Name',
                'redirectUris' => [
                    'http://localhost:4000/callback',
                    'http://localhost:4000/silent-callback',
                ],
            ]);

        $this->assertDatabaseHas('oauth_clients', [
            'id' => $client->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_can_disable_client(): void
    {
        $client = Client::create([
            'id' => 'disable-client',
            'realm_id' => $this->realm->id,
            'client_id' => 'disable-client',
            'name' => 'Disable Test',
            'secret' => 'secret',
            'redirect_uris' => json_encode(['http://localhost:3000/callback']),
            'grant_types' => 'authorization_code',

            'personal_access_client' => false,
            'password_client' => false,
            'revoked' => false,
            'client_type' => 'confidential',
            'enabled' => true,
        ]);

        $response = $this->putJson("/api/admin/realms/{$this->realm->name}/clients/{$client->id}", [
            'enabled' => false,
        ]);

        $response->assertStatus(200)
            ->assertJson(['enabled' => false]);

        $this->assertDatabaseHas('oauth_clients', [
            'id' => $client->id,
            'enabled' => false,
        ]);
    }

    public function test_can_delete_client(): void
    {
        $client = Client::create([
            'id' => 'delete-client',
            'realm_id' => $this->realm->id,
            'client_id' => 'delete-client',
            'name' => 'Delete Test',
            'secret' => 'secret',
            'redirect_uris' => json_encode(['http://localhost:3000/callback']),
            'grant_types' => 'authorization_code',

            'personal_access_client' => false,
            'password_client' => false,
            'revoked' => false,
            'client_type' => 'confidential',
            'enabled' => true,
        ]);

        $response = $this->deleteJson("/api/admin/realms/{$this->realm->name}/clients/{$client->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('oauth_clients', ['id' => 'delete-client']);
    }

    public function test_cannot_delete_nonexistent_client(): void
    {
        $response = $this->deleteJson("/api/admin/realms/{$this->realm->name}/clients/nonexistent");
        $response->assertStatus(404);
    }

    public function test_redirect_uris_validation(): void
    {
        $data = [
            'clientId' => 'invalid-redirect-client',
            'name' => 'Invalid Redirect',
            'redirectUris' => [
                'not-a-valid-url',
                'http://valid.com',
            ],
        ];

        $response = $this->postJson("/api/admin/realms/{$this->realm->name}/clients", $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['redirectUris.0']);
    }
}
