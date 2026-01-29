<?php

namespace Tests\Feature;

use App\Models\Realm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserApiTest extends TestCase
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

    public function test_can_list_users_in_realm(): void
    {
        User::create([
            'realm_id' => $this->realm->id,
            'username' => 'user1',
            'name' => 'User One',
            'email' => 'user1@example.com',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'realm_id' => $this->realm->id,
            'username' => 'user2',
            'name' => 'User Two',
            'email' => 'user2@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->getJson("/api/admin/realms/{$this->realm->name}/users");

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'username',
                    'email',
                    'firstName',
                    'lastName',
                    'enabled',
                    'emailVerified',
                ]
            ]);
    }

    public function test_can_search_users_by_username(): void
    {
        User::create([
            'realm_id' => $this->realm->id,
            'username' => 'john.doe',
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'realm_id' => $this->realm->id,
            'username' => 'jane.smith',
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->getJson("/api/admin/realms/{$this->realm->name}/users?username=john.doe");

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonPath('0.username', 'john.doe');
    }

    public function test_can_search_users_by_email(): void
    {
        User::create([
            'realm_id' => $this->realm->id,
            'username' => 'user1',
            'name' => 'User One',
            'email' => 'specific@example.com',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'realm_id' => $this->realm->id,
            'username' => 'user2',
            'name' => 'User Two',
            'email' => 'other@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->getJson("/api/admin/realms/{$this->realm->name}/users?email=specific@example.com");

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonPath('0.email', 'specific@example.com');
    }

    public function test_can_search_users_with_search_parameter(): void
    {
        User::create([
            'realm_id' => $this->realm->id,
            'username' => 'testuser',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'realm_id' => $this->realm->id,
            'username' => 'other',
            'name' => 'Other User',
            'email' => 'other@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->getJson("/api/admin/realms/{$this->realm->name}/users?search=test");

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonPath('0.username', 'testuser');
    }

    public function test_pagination_works_for_user_list(): void
    {
        for ($i = 1; $i <= 15; $i++) {
            User::create([
                'realm_id' => $this->realm->id,
                'username' => "user{$i}",
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'password' => Hash::make('password'),
            ]);
        }

        $response = $this->getJson("/api/admin/realms/{$this->realm->name}/users?first=0&max=5");
        $response->assertStatus(200)->assertJsonCount(5);

        $response = $this->getJson("/api/admin/realms/{$this->realm->name}/users?first=5&max=5");
        $response->assertStatus(200)->assertJsonCount(5);
    }

    public function test_max_pagination_limit_is_enforced(): void
    {
        for ($i = 1; $i <= 150; $i++) {
            User::create([
                'realm_id' => $this->realm->id,
                'username' => "user{$i}",
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'password' => Hash::make('password'),
            ]);
        }

        $response = $this->getJson("/api/admin/realms/{$this->realm->name}/users?max=200");
        $response->assertStatus(200)->assertJsonCount(100);
    }

    public function test_can_get_user_by_id(): void
    {
        $user = User::create([
            'realm_id' => $this->realm->id,
            'username' => 'testuser',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $response = $this->getJson("/api/admin/realms/{$this->realm->name}/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJson([
                'username' => 'testuser',
                'email' => 'test@example.com',
                'emailVerified' => true,
            ]);
    }

    public function test_returns_404_for_nonexistent_user(): void
    {
        $response = $this->getJson("/api/admin/realms/{$this->realm->name}/users/99999");
        $response->assertStatus(404);
    }

    public function test_can_create_new_user(): void
    {
        $data = [
            'username' => 'newuser',
            'email' => 'newuser@example.com',
            'firstName' => 'New',
            'lastName' => 'User',
            'enabled' => true,
            'credentials' => [
                [
                    'type' => 'password',
                    'value' => 'SecurePassword123',
                    'temporary' => false,
                ]
            ],
        ];

        $response = $this->postJson("/api/admin/realms/{$this->realm->name}/users", $data);

        $response->assertStatus(201)
            ->assertJson([
                'username' => 'newuser',
                'email' => 'newuser@example.com',
                'firstName' => 'New',
                'lastName' => 'User',
            ]);

        $this->assertDatabaseHas('users', [
            'realm_id' => $this->realm->id,
            'username' => 'newuser',
            'email' => 'newuser@example.com',
        ]);
    }

    public function test_user_creation_enforces_realm_unique_username(): void
    {
        User::create([
            'realm_id' => $this->realm->id,
            'username' => 'existing',
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson("/api/admin/realms/{$this->realm->name}/users", [
            'username' => 'existing',
            'email' => 'different@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['username']);
    }

    public function test_user_creation_enforces_realm_unique_email(): void
    {
        User::create([
            'realm_id' => $this->realm->id,
            'username' => 'existing',
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson("/api/admin/realms/{$this->realm->name}/users", [
            'username' => 'different',
            'email' => 'existing@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_can_update_user(): void
    {
        $user = User::create([
            'realm_id' => $this->realm->id,
            'username' => 'olduser',
            'name' => 'Old Name',
            'email' => 'old@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->putJson("/api/admin/realms/{$this->realm->name}/users/{$user->id}", [
            'firstName' => 'Updated',
            'lastName' => 'Name',
            'email' => 'updated@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'firstName' => 'Updated',
                'lastName' => 'Name',
                'email' => 'updated@example.com',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_can_update_user_attributes(): void
    {
        $user = User::create([
            'realm_id' => $this->realm->id,
            'username' => 'testuser',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->putJson("/api/admin/realms/{$this->realm->name}/users/{$user->id}", [
            'attributes' => [
                'department' => ['Engineering'],
                'role' => ['Developer'],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('attributes.department', ['Engineering'])
            ->assertJsonPath('attributes.role', ['Developer']);
    }

    public function test_can_delete_user(): void
    {
        $user = User::create([
            'realm_id' => $this->realm->id,
            'username' => 'deleteuser',
            'name' => 'Delete User',
            'email' => 'delete@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->deleteJson("/api/admin/realms/{$this->realm->name}/users/{$user->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_cannot_delete_nonexistent_user(): void
    {
        $response = $this->deleteJson("/api/admin/realms/{$this->realm->name}/users/99999");
        $response->assertStatus(404);
    }
}
