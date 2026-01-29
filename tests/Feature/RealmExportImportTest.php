<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Group;
use App\Models\Realm;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RealmExportImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('passport:keys', ['--force' => true]);
    }

    public function test_export_generates_valid_json_structure(): void
    {
        $realm = $this->createTestRealm();

        $response = $this->getJson("/api/admin/realms/{$realm->name}/export");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'realm',
                'displayName',
                'enabled',
                'accessTokenLifespan',
                'refreshTokenLifespan',
                'ssoSessionIdleTimeout',
                'ssoSessionMaxLifespan',
                'clients',
                'roles' => [
                    'realm',
                    'client',
                ],
                'groups',
                'users',
            ]);

        $this->assertEquals($realm->name, $response->json('realm'));
    }

    public function test_export_includes_clients_roles_groups(): void
    {
        $realm = $this->createTestRealm();
        
        $client = Client::create([
            'id' => 'test-client',
            'realm_id' => $realm->id,
            'client_id' => 'test-client',
            'name' => 'Test Client',
            'secret' => 'secret123',
            'redirect_uris' => json_encode(['https://example.com/callback']),
            'grant_types' => ['authorization_code'],
            'enabled' => true,
            'revoked' => false,
        ]);

        $realmRole = Role::create([
            'realm_id' => $realm->id,
            'name' => 'admin',
            'description' => 'Admin role',
            'composite' => false,
        ]);

        $clientRole = Role::create([
            'realm_id' => $realm->id,
            'client_id' => $client->id,
            'name' => 'client-admin',
            'description' => 'Client admin role',
            'composite' => false,
        ]);

        $group = Group::create([
            'realm_id' => $realm->id,
            'name' => 'Admins',
            'path' => '/Admins',
        ]);

        $response = $this->getJson("/api/admin/realms/{$realm->name}/export");

        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertCount(1, $data['clients']);
        $this->assertEquals('test-client', $data['clients'][0]['clientId']);
        
        $this->assertCount(1, $data['roles']['realm']);
        $this->assertEquals('admin', $data['roles']['realm'][0]['name']);
        
        $this->assertArrayHasKey('test-client', $data['roles']['client']);
        $this->assertCount(1, $data['roles']['client']['test-client']);
        $this->assertEquals('client-admin', $data['roles']['client']['test-client'][0]['name']);
        
        $this->assertCount(1, $data['groups']);
        $this->assertEquals('Admins', $data['groups'][0]['name']);
    }

    public function test_export_with_users(): void
    {
        $realm = $this->createTestRealm();
        
        User::create([
            'realm_id' => $realm->id,
            'username' => 'testuser',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->getJson("/api/admin/realms/{$realm->name}/export?includeUsers=true");

        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertCount(1, $data['users']);
        $this->assertEquals('testuser', $data['users'][0]['username']);
        $this->assertArrayHasKey('credentials', $data['users'][0]);
    }

    public function test_export_without_users(): void
    {
        $realm = $this->createTestRealm();
        
        User::create([
            'realm_id' => $realm->id,
            'username' => 'testuser',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->getJson("/api/admin/realms/{$realm->name}/export?includeUsers=false");

        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertEmpty($data['users']);
    }

    public function test_import_creates_realm_correctly(): void
    {
        $realmData = [
            'realm' => 'imported-realm',
            'displayName' => 'Imported Realm',
            'enabled' => true,
            'accessTokenLifespan' => 600,
            'refreshTokenLifespan' => 3600,
            'ssoSessionIdleTimeout' => 1800,
            'ssoSessionMaxLifespan' => 36000,
            'clients' => [],
            'roles' => ['realm' => [], 'client' => []],
            'groups' => [],
            'users' => [],
        ];

        $response = $this->postJson('/api/admin/realms/import', [
            'realm' => $realmData,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Realm imported successfully',
                'realm' => 'imported-realm',
            ]);

        $this->assertDatabaseHas('realms', [
            'name' => 'imported-realm',
            'display_name' => 'Imported Realm',
            'access_token_lifespan' => 600,
        ]);
    }

    public function test_import_creates_all_components(): void
    {
        $realmData = [
            'realm' => 'full-realm',
            'displayName' => 'Full Realm',
            'enabled' => true,
            'accessTokenLifespan' => 300,
            'clients' => [
                [
                    'id' => 'app-client',
                    'clientId' => 'app-client',
                    'name' => 'Application Client',
                    'secret' => 'client-secret',
                    'redirectUris' => ['https://app.example.com/callback'],
                    'enabled' => true,
                ],
            ],
            'roles' => [
                'realm' => [
                    ['name' => 'user', 'description' => 'User role', 'composite' => false],
                    ['name' => 'admin', 'description' => 'Admin role', 'composite' => false],
                ],
                'client' => [
                    'app-client' => [
                        ['name' => 'view', 'description' => 'View permission', 'composite' => false],
                    ],
                ],
            ],
            'groups' => [
                [
                    'name' => 'Users',
                    'path' => '/Users',
                    'realmRoles' => ['user'],
                    'clientRoles' => [],
                ],
            ],
            'users' => [
                [
                    'username' => 'john',
                    'email' => 'john@example.com',
                    'emailVerified' => true,
                    'enabled' => true,
                    'credentials' => [
                        [
                            'type' => 'password',
                            'hashedSaltedValue' => Hash::make('password'),
                        ],
                    ],
                    'realmRoles' => ['user'],
                    'clientRoles' => [],
                    'groups' => ['/Users'],
                ],
            ],
        ];

        $response = $this->postJson('/api/admin/realms/import', [
            'realm' => $realmData,
        ]);

        $response->assertStatus(201);

        $realm = Realm::where('name', 'full-realm')->first();
        $this->assertNotNull($realm);

        $this->assertDatabaseHas('oauth_clients', [
            'realm_id' => $realm->id,
            'client_id' => 'app-client',
        ]);

        $this->assertDatabaseHas('crownid_roles', [
            'realm_id' => $realm->id,
            'name' => 'user',
            'client_id' => null,
        ]);

        $this->assertDatabaseHas('crownid_roles', [
            'realm_id' => $realm->id,
            'name' => 'admin',
        ]);

        $client = Client::where('realm_id', $realm->id)
            ->where('client_id', 'app-client')
            ->first();
        $this->assertDatabaseHas('crownid_roles', [
            'realm_id' => $realm->id,
            'client_id' => $client->id,
            'name' => 'view',
        ]);

        $this->assertDatabaseHas('groups', [
            'realm_id' => $realm->id,
            'name' => 'Users',
            'path' => '/Users',
        ]);

        $this->assertDatabaseHas('users', [
            'realm_id' => $realm->id,
            'username' => 'john',
            'email' => 'john@example.com',
        ]);

        $user = User::where('realm_id', $realm->id)
            ->where('username', 'john')
            ->first();
        $userRole = Role::where('realm_id', $realm->id)
            ->where('name', 'user')
            ->first();
        
        $this->assertTrue($user->directRoles->contains($userRole));

        $group = Group::where('realm_id', $realm->id)
            ->where('path', '/Users')
            ->first();
        $this->assertTrue($user->groups->contains($group));
        $this->assertTrue($group->roles->contains($userRole));
    }

    public function test_round_trip_export_import(): void
    {
        $realm = $this->createFullTestRealm();

        $exportResponse = $this->getJson("/api/admin/realms/{$realm->name}/export?includeUsers=true");
        $exportResponse->assertStatus(200);
        $exportedData = $exportResponse->json();

        $realm->delete();

        $importResponse = $this->postJson('/api/admin/realms/import', [
            'realm' => $exportedData,
        ]);
        $importResponse->assertStatus(201);

        $reimportedRealm = Realm::where('name', $exportedData['realm'])->first();
        $this->assertNotNull($reimportedRealm);
        $this->assertEquals($exportedData['displayName'], $reimportedRealm->display_name);
        $this->assertEquals($exportedData['accessTokenLifespan'], $reimportedRealm->access_token_lifespan);

        $this->assertCount(count($exportedData['clients']), $reimportedRealm->clients);
        $this->assertCount(count($exportedData['roles']['realm']), 
            $reimportedRealm->roles()->whereNull('client_id')->get());
        $this->assertCount(count($exportedData['groups']), $reimportedRealm->groups);
        $this->assertCount(count($exportedData['users']), $reimportedRealm->users);
    }

    public function test_import_handles_composite_roles(): void
    {
        $realmData = [
            'realm' => 'composite-realm',
            'displayName' => 'Composite Realm',
            'enabled' => true,
            'roles' => [
                'realm' => [
                    ['name' => 'user', 'description' => 'User role', 'composite' => false],
                    ['name' => 'admin', 'description' => 'Admin role', 'composite' => false],
                    [
                        'name' => 'super-admin',
                        'description' => 'Super admin role',
                        'composite' => true,
                        'composites' => [
                            'realm' => ['user', 'admin'],
                            'client' => [],
                        ],
                    ],
                ],
                'client' => [],
            ],
            'clients' => [],
            'groups' => [],
            'users' => [],
        ];

        $response = $this->postJson('/api/admin/realms/import', [
            'realm' => $realmData,
        ]);

        $response->assertStatus(201);

        $realm = Realm::where('name', 'composite-realm')->first();
        $superAdminRole = Role::where('realm_id', $realm->id)
            ->where('name', 'super-admin')
            ->first();

        $this->assertTrue($superAdminRole->composite);
        $this->assertCount(2, $superAdminRole->childRoles);
        $this->assertTrue($superAdminRole->childRoles->pluck('name')->contains('user'));
        $this->assertTrue($superAdminRole->childRoles->pluck('name')->contains('admin'));
    }

    public function test_export_includes_composite_roles(): void
    {
        $realm = $this->createTestRealm();
        
        $userRole = Role::create([
            'realm_id' => $realm->id,
            'name' => 'user',
            'composite' => false,
        ]);

        $adminRole = Role::create([
            'realm_id' => $realm->id,
            'name' => 'admin',
            'composite' => false,
        ]);

        $superRole = Role::create([
            'realm_id' => $realm->id,
            'name' => 'super-admin',
            'composite' => true,
        ]);

        $superRole->childRoles()->attach([$userRole->id, $adminRole->id]);

        $response = $this->getJson("/api/admin/realms/{$realm->name}/export");

        $response->assertStatus(200);
        
        $data = $response->json();
        $superRoleData = collect($data['roles']['realm'])->firstWhere('name', 'super-admin');
        
        $this->assertNotNull($superRoleData);
        $this->assertTrue($superRoleData['composite']);
        $this->assertArrayHasKey('composites', $superRoleData);
        $this->assertContains('user', $superRoleData['composites']['realm']);
        $this->assertContains('admin', $superRoleData['composites']['realm']);
    }

    public function test_cli_export_command(): void
    {
        $realm = $this->createTestRealm();
        
        $filename = storage_path('app/test-export.json');
        
        if (File::exists($filename)) {
            File::delete($filename);
        }

        $this->artisan('crownid:export', [
            'realm' => $realm->name,
            '--file' => $filename,
        ])->assertExitCode(0);

        $this->assertTrue(File::exists($filename));
        
        $content = File::get($filename);
        $data = json_decode($content, true);
        
        $this->assertEquals($realm->name, $data['realm']);
        
        File::delete($filename);
    }

    public function test_cli_import_command(): void
    {
        $realmData = [
            'realm' => 'cli-imported',
            'displayName' => 'CLI Imported',
            'enabled' => true,
            'accessTokenLifespan' => 300,
            'clients' => [],
            'roles' => ['realm' => [], 'client' => []],
            'groups' => [],
            'users' => [],
        ];

        $filename = storage_path('app/test-import.json');
        File::put($filename, json_encode($realmData));

        $this->artisan('crownid:import', [
            'file' => $filename,
        ])->assertExitCode(0);

        $this->assertDatabaseHas('realms', [
            'name' => 'cli-imported',
        ]);

        File::delete($filename);
    }

    public function test_cli_import_directory_command(): void
    {
        $directory = storage_path('app/test-realms');
        File::makeDirectory($directory, 0755, true, true);

        $realm1 = [
            'realm' => 'dir-realm-1',
            'displayName' => 'Directory Realm 1',
            'enabled' => true,
            'clients' => [],
            'roles' => ['realm' => [], 'client' => []],
            'groups' => [],
            'users' => [],
        ];

        $realm2 = [
            'realm' => 'dir-realm-2',
            'displayName' => 'Directory Realm 2',
            'enabled' => true,
            'clients' => [],
            'roles' => ['realm' => [], 'client' => []],
            'groups' => [],
            'users' => [],
        ];

        File::put($directory . '/realm1.json', json_encode($realm1));
        File::put($directory . '/realm2.json', json_encode($realm2));
        File::put($directory . '/not-json.txt', 'ignored');

        $this->artisan('crownid:import-directory', [
            'directory' => $directory,
        ])->assertExitCode(0);

        $this->assertDatabaseHas('realms', ['name' => 'dir-realm-1']);
        $this->assertDatabaseHas('realms', ['name' => 'dir-realm-2']);

        File::deleteDirectory($directory);
    }

    public function test_export_maintains_group_hierarchy(): void
    {
        $realm = $this->createTestRealm();
        
        $parent = Group::create([
            'realm_id' => $realm->id,
            'name' => 'Engineering',
            'path' => '/Engineering',
        ]);

        $child = Group::create([
            'realm_id' => $realm->id,
            'parent_id' => $parent->id,
            'name' => 'Backend',
            'path' => '/Engineering/Backend',
        ]);

        $response = $this->getJson("/api/admin/realms/{$realm->name}/export");

        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertCount(1, $data['groups']);
        
        $parentData = $data['groups'][0];
        $this->assertEquals('Engineering', $parentData['name']);
        $this->assertArrayHasKey('subGroups', $parentData);
        $this->assertCount(1, $parentData['subGroups']);
        $this->assertEquals('Backend', $parentData['subGroups'][0]['name']);
    }

    public function test_import_handles_missing_users_gracefully(): void
    {
        $realmData = [
            'realm' => 'no-users-realm',
            'displayName' => 'No Users Realm',
            'enabled' => true,
            'clients' => [],
            'roles' => ['realm' => [], 'client' => []],
            'groups' => [],
        ];

        $response = $this->postJson('/api/admin/realms/import', [
            'realm' => $realmData,
        ]);

        $response->assertStatus(201);
        
        $realm = Realm::where('name', 'no-users-realm')->first();
        $this->assertNotNull($realm);
        $this->assertCount(0, $realm->users);
    }

    protected function createTestRealm(): Realm
    {
        return Realm::create([
            'name' => 'test-realm',
            'display_name' => 'Test Realm',
            'enabled' => true,
            'access_token_lifespan' => 300,
            'refresh_token_lifespan' => 1800,
            'sso_session_idle_timeout' => 1800,
            'sso_session_max_lifespan' => 36000,
        ]);
    }

    protected function createFullTestRealm(): Realm
    {
        $realm = $this->createTestRealm();

        $client = Client::create([
            'id' => 'full-client',
            'realm_id' => $realm->id,
            'client_id' => 'full-client',
            'name' => 'Full Client',
            'secret' => 'secret',
            'redirect_uris' => json_encode(['https://example.com']),
            'grant_types' => ['authorization_code'],
            'enabled' => true,
            'revoked' => false,
        ]);

        $role = Role::create([
            'realm_id' => $realm->id,
            'name' => 'test-role',
            'composite' => false,
        ]);

        $group = Group::create([
            'realm_id' => $realm->id,
            'name' => 'TestGroup',
            'path' => '/TestGroup',
        ]);

        $user = User::create([
            'realm_id' => $realm->id,
            'username' => 'fulluser',
            'name' => 'Full User',
            'email' => 'full@example.com',
            'password' => Hash::make('password'),
        ]);

        return $realm;
    }
}
