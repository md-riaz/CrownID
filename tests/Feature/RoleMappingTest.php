<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Group;
use App\Models\Realm;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleMappingTest extends TestCase
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
            'revoked' => false,
        ]);

        $this->user = User::create([
            'realm_id' => $this->realm->id,
            'username' => 'testuser',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->client = Client::create([
            'id' => 'test-client',
            'client_id' => 'test-client',
            'realm_id' => $this->realm->id,
            'name' => 'Test Client',
            'secret' => 'secret',
            'redirect_uris' => json_encode(['http://localhost:3000/callback']),
            'grant_types' => ['authorization_code', 'refresh_token'],
            'enabled' => true,
            'revoked' => false,
        ]);
    }

    public function test_can_assign_realm_role_to_user(): void
    {
        $role = Role::create([
            'realm_id' => $this->realm->id,
            'name' => 'admin',
        ]);

        $response = $this->postJson("/api/admin/realms/{$this->realm->name}/users/{$this->user->id}/role-mappings/realm", [
            [
                'id' => $role->id,
                'name' => 'admin',
            ]
        ]);

        $response->assertStatus(204);
        $this->assertDatabaseHas('crownid_role_user', [
            'role_id' => $role->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_can_remove_realm_role_from_user(): void
    {
        $role = Role::create([
            'realm_id' => $this->realm->id,
            'name' => 'admin',
        ]);

        $this->user->directRoles()->attach($role->id);

        $response = $this->deleteJson("/api/admin/realms/{$this->realm->name}/users/{$this->user->id}/role-mappings/realm", [
            [
                'id' => $role->id,
                'name' => 'admin',
            ]
        ]);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('crownid_role_user', [
            'role_id' => $role->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_can_assign_client_role_to_user(): void
    {
        $role = Role::create([
            'realm_id' => $this->realm->id,
            'client_id' => $this->client->id,
            'name' => 'viewer',
        ]);

        $response = $this->postJson("/api/admin/realms/{$this->realm->name}/users/{$this->user->id}/role-mappings/clients/{$this->client->id}", [
            [
                'id' => $role->id,
                'name' => 'viewer',
            ]
        ]);

        $response->assertStatus(204);
        $this->assertDatabaseHas('crownid_role_user', [
            'role_id' => $role->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_can_get_user_role_mappings(): void
    {
        $realmRole = Role::create([
            'realm_id' => $this->realm->id,
            'name' => 'admin',
        ]);

        $clientRole = Role::create([
            'realm_id' => $this->realm->id,
            'client_id' => $this->client->id,
            'name' => 'viewer',
        ]);

        $this->user->directRoles()->attach([$realmRole->id, $clientRole->id]);

        $response = $this->getJson("/api/admin/realms/{$this->realm->name}/users/{$this->user->id}/role-mappings");

        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'admin']);
        $response->assertJsonFragment(['name' => 'viewer']);
    }

    public function test_user_inherits_roles_from_group(): void
    {
        $role = Role::create([
            'realm_id' => $this->realm->id,
            'name' => 'admin',
        ]);

        $group = Group::create([
            'realm_id' => $this->realm->id,
            'name' => 'Admins',
            'path' => '/Admins',
        ]);

        $group->roles()->attach($role->id);
        $this->user->groups()->attach($group->id);

        $allRoles = $this->user->getAllRoles();
        $roleNames = array_map(fn($r) => $r->name, $allRoles);

        $this->assertContains('admin', $roleNames);
    }

    public function test_can_add_user_to_group(): void
    {
        $group = Group::create([
            'realm_id' => $this->realm->id,
            'name' => 'Admins',
            'path' => '/Admins',
        ]);

        $response = $this->postJson("/api/admin/realms/{$this->realm->name}/users/{$this->user->id}/groups", [
            'groupId' => $group->id,
        ]);

        $response->assertStatus(204);
        $this->assertDatabaseHas('group_user', [
            'group_id' => $group->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_can_remove_user_from_group(): void
    {
        $group = Group::create([
            'realm_id' => $this->realm->id,
            'name' => 'Admins',
            'path' => '/Admins',
        ]);

        $this->user->groups()->attach($group->id);

        $response = $this->deleteJson("/api/admin/realms/{$this->realm->name}/users/{$this->user->id}/groups/{$group->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('group_user', [
            'group_id' => $group->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_can_list_user_groups(): void
    {
        $group = Group::create([
            'realm_id' => $this->realm->id,
            'name' => 'Admins',
            'path' => '/Admins',
        ]);

        $this->user->groups()->attach($group->id);

        $response = $this->getJson("/api/admin/realms/{$this->realm->name}/users/{$this->user->id}/groups");

        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'Admins']);
    }
}
