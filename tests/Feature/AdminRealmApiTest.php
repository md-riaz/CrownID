<?php

namespace Tests\Feature;

use App\Models\Realm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRealmApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_all_realms(): void
    {
        Realm::create(['name' => 'realm1', 'display_name' => 'Realm 1', 'enabled' => true]);
        Realm::create(['name' => 'realm2', 'display_name' => 'Realm 2', 'enabled' => true]);

        $response = $this->getJson('/api/admin/realms');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'realm',
                        'displayName',
                        'enabled',
                        'accessTokenLifespan',
                        'refreshTokenLifespan',
                    ]
                ]
            ]);
    }

    public function test_can_get_realm_by_name(): void
    {
        $realm = Realm::create([
            'name' => 'test-realm',
            'display_name' => 'Test Realm',
            'enabled' => true,
            'access_token_lifespan' => 300,
            'refresh_token_lifespan' => 1800,
        ]);

        $response = $this->getJson("/api/admin/realms/{$realm->name}");

        $response->assertStatus(200)
            ->assertJson([
                'realm' => 'test-realm',
                'displayName' => 'Test Realm',
                'enabled' => true,
                'accessTokenLifespan' => 300,
                'refreshTokenLifespan' => 1800,
            ]);
    }

    public function test_returns_404_for_nonexistent_realm(): void
    {
        $response = $this->getJson('/api/admin/realms/nonexistent');
        $response->assertStatus(404);
    }

    public function test_can_create_new_realm(): void
    {
        $data = [
            'realm' => 'new-realm',
            'displayName' => 'New Realm',
            'enabled' => true,
            'accessTokenLifespan' => 600,
            'refreshTokenLifespan' => 3600,
        ];

        $response = $this->postJson('/api/admin/realms', $data);

        $response->assertStatus(201)
            ->assertJson([
                'realm' => 'new-realm',
                'displayName' => 'New Realm',
                'enabled' => true,
                'accessTokenLifespan' => 600,
                'refreshTokenLifespan' => 3600,
            ]);

        $this->assertDatabaseHas('realms', [
            'name' => 'new-realm',
            'display_name' => 'New Realm',
        ]);
    }

    public function test_realm_creation_requires_unique_name(): void
    {
        Realm::create(['name' => 'existing-realm', 'display_name' => 'Existing', 'enabled' => true]);

        $response = $this->postJson('/api/admin/realms', [
            'realm' => 'existing-realm',
            'displayName' => 'Another Realm',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['realm']);
    }

    public function test_realm_creation_sets_default_values(): void
    {
        $response = $this->postJson('/api/admin/realms', [
            'realm' => 'minimal-realm',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'realm' => 'minimal-realm',
                'displayName' => 'minimal-realm',
                'enabled' => true,
            ]);
    }

    public function test_can_update_realm(): void
    {
        $realm = Realm::create([
            'name' => 'update-realm',
            'display_name' => 'Original',
            'enabled' => true,
        ]);

        $response = $this->putJson("/api/admin/realms/{$realm->name}", [
            'displayName' => 'Updated Realm',
            'accessTokenLifespan' => 900,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'realm' => 'update-realm',
                'displayName' => 'Updated Realm',
                'accessTokenLifespan' => 900,
            ]);

        $this->assertDatabaseHas('realms', [
            'name' => 'update-realm',
            'display_name' => 'Updated Realm',
        ]);
    }

    public function test_can_disable_realm(): void
    {
        $realm = Realm::create([
            'name' => 'disable-realm',
            'display_name' => 'Disable Test',
            'enabled' => true,
        ]);

        $response = $this->putJson("/api/admin/realms/{$realm->name}", [
            'enabled' => false,
        ]);

        $response->assertStatus(200)
            ->assertJson(['enabled' => false]);

        $this->assertDatabaseHas('realms', [
            'name' => 'disable-realm',
            'enabled' => false,
        ]);
    }

    public function test_can_delete_realm(): void
    {
        $realm = Realm::create([
            'name' => 'delete-realm',
            'display_name' => 'To Delete',
            'enabled' => true,
        ]);

        $response = $this->deleteJson("/api/admin/realms/{$realm->name}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('realms', ['name' => 'delete-realm']);
    }

    public function test_cannot_delete_nonexistent_realm(): void
    {
        $response = $this->deleteJson('/api/admin/realms/nonexistent');
        $response->assertStatus(404);
    }
}
