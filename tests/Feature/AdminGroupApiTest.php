<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Realm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminGroupApiTest extends TestCase
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

    public function test_can_list_groups(): void
    {
        Group::create([
            'realm_id' => $this->realm->id,
            'name' => 'Admins',
            'path' => '/Admins',
        ]);

        Group::create([
            'realm_id' => $this->realm->id,
            'name' => 'Users',
            'path' => '/Users',
        ]);

        $response = $this->getJson("/api/admin/realms/{$this->realm->name}/groups");

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
        $response->assertJsonFragment(['name' => 'Admins']);
        $response->assertJsonFragment(['name' => 'Users']);
    }

    public function test_can_create_group(): void
    {
        $response = $this->postJson("/api/admin/realms/{$this->realm->name}/groups", [
            'name' => 'Moderators',
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment(['name' => 'Moderators']);
        $response->assertJsonFragment(['path' => '/Moderators']);
        $this->assertDatabaseHas('groups', [
            'realm_id' => $this->realm->id,
            'name' => 'Moderators',
            'path' => '/Moderators',
        ]);
    }

    public function test_can_create_subgroup(): void
    {
        $parent = Group::create([
            'realm_id' => $this->realm->id,
            'name' => 'Admins',
            'path' => '/Admins',
        ]);

        $response = $this->postJson("/api/admin/realms/{$this->realm->name}/groups", [
            'name' => 'Super',
            'parent' => '/Admins',
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment(['name' => 'Super']);
        $response->assertJsonFragment(['path' => '/Admins/Super']);
        $this->assertDatabaseHas('groups', [
            'realm_id' => $this->realm->id,
            'name' => 'Super',
            'path' => '/Admins/Super',
            'parent_id' => $parent->id,
        ]);
    }

    public function test_can_get_group_by_id(): void
    {
        $group = Group::create([
            'realm_id' => $this->realm->id,
            'name' => 'Admins',
            'path' => '/Admins',
        ]);

        $response = $this->getJson("/api/admin/realms/{$this->realm->name}/groups/{$group->id}");

        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'Admins']);
    }

    public function test_can_delete_group(): void
    {
        $group = Group::create([
            'realm_id' => $this->realm->id,
            'name' => 'Admins',
            'path' => '/Admins',
        ]);

        $response = $this->deleteJson("/api/admin/realms/{$this->realm->name}/groups/{$group->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('groups', [
            'id' => $group->id,
        ]);
    }
}
