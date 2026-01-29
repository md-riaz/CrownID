<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Group;
use App\Models\Realm;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRoleApiTest extends TestCase
{
    use RefreshDatabase;

    protected Realm $realm;
    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->realm = Realm::create([
            'name' => 'test-realm',
            'display_name' => 'Test Realm',
            'enabled' => true,
            'revoked' => false,
        ]);

        $this->client = Client::create([
            'id' => 'test-client-id',
            'client_id' => 'test-client-id',
            'realm_id' => $this->realm->id,
            'name' => 'Test Client',
            'secret' => 'test-secret',
            'redirect_uris' => json_encode(['http://localhost:3000/callback']),
            'grant_types' => ['authorization_code', 'refresh_token'],
            'enabled' => true,
            'revoked' => false,
        ]);
    }

    public function test_can_list_realm_roles(): void
    {
        Role::create([
            'realm_id' => $this->realm->id,
            'name' => 'admin',
            'description' => 'Admin role',
        ]);

        Role::create([
            'realm_id' => $this->realm->id,
            'name' => 'user',
            'description' => 'User role',
        ]);

        $response = $this->getJson("/api/admin/realms/{$this->realm->name}/roles");

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
        $response->assertJsonFragment(['name' => 'admin']);
        $response->assertJsonFragment(['name' => 'user']);
    }

    public function test_can_create_realm_role(): void
    {
        $response = $this->postJson("/api/admin/realms/{$this->realm->name}/roles", [
            'name' => 'moderator',
            'description' => 'Moderator role',
            'composite' => false,
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment(['name' => 'moderator']);
        $this->assertDatabaseHas('crownid_roles', [
            'realm_id' => $this->realm->id,
            'name' => 'moderator',
            'client_id' => null,
        ]);
    }

    public function test_can_get_realm_role_by_name(): void
    {
        Role::create([
            'realm_id' => $this->realm->id,
            'name' => 'admin',
            'description' => 'Admin role',
        ]);

        $response = $this->getJson("/api/admin/realms/{$this->realm->name}/roles/admin");

        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'admin']);
    }

    public function test_can_delete_realm_role(): void
    {
        Role::create([
            'realm_id' => $this->realm->id,
            'name' => 'admin',
        ]);

        $response = $this->deleteJson("/api/admin/realms/{$this->realm->name}/roles/admin");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('crownid_roles', [
            'realm_id' => $this->realm->id,
            'name' => 'admin',
        ]);
    }

    public function test_can_list_client_roles(): void
    {
        Role::create([
            'realm_id' => $this->realm->id,
            'client_id' => $this->client->id,
            'name' => 'viewer',
        ]);

        Role::create([
            'realm_id' => $this->realm->id,
            'client_id' => $this->client->id,
            'name' => 'editor',
        ]);

        $response = $this->getJson("/api/admin/realms/{$this->realm->name}/clients/{$this->client->id}/roles");

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
        $response->assertJsonFragment(['name' => 'viewer']);
        $response->assertJsonFragment(['name' => 'editor']);
    }

    public function test_can_create_client_role(): void
    {
        $response = $this->postJson("/api/admin/realms/{$this->realm->name}/clients/{$this->client->id}/roles", [
            'name' => 'reviewer',
            'description' => 'Reviewer role',
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment(['name' => 'reviewer']);
        $this->assertDatabaseHas('crownid_roles', [
            'realm_id' => $this->realm->id,
            'client_id' => $this->client->id,
            'name' => 'reviewer',
        ]);
    }

    public function test_can_get_client_role_by_name(): void
    {
        Role::create([
            'realm_id' => $this->realm->id,
            'client_id' => $this->client->id,
            'name' => 'viewer',
        ]);

        $response = $this->getJson("/api/admin/realms/{$this->realm->name}/clients/{$this->client->id}/roles/viewer");

        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'viewer']);
    }

    public function test_can_delete_client_role(): void
    {
        Role::create([
            'realm_id' => $this->realm->id,
            'client_id' => $this->client->id,
            'name' => 'viewer',
        ]);

        $response = $this->deleteJson("/api/admin/realms/{$this->realm->name}/clients/{$this->client->id}/roles/viewer");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('crownid_roles', [
            'client_id' => $this->client->id,
            'name' => 'viewer',
        ]);
    }
}
