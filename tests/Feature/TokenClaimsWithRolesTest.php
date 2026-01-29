<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Group;
use App\Models\Realm;
use App\Models\Role;
use App\Models\User;
use App\Services\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token\Parser;
use Tests\TestCase;

class TokenClaimsWithRolesTest extends TestCase
{
    use RefreshDatabase;

    protected Realm $realm;
    protected User $user;
    protected Client $client;
    protected JwtService $jwtService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->realm = Realm::create([
            'name' => 'test-realm',
            'display_name' => 'Test Realm',
            'enabled' => true,
            'revoked' => false,
            'access_token_lifespan' => 300,
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

        $this->jwtService = app(JwtService::class);
    }

    public function test_access_token_includes_realm_roles(): void
    {
        $adminRole = Role::create([
            'realm_id' => $this->realm->id,
            'name' => 'admin',
        ]);

        $userRole = Role::create([
            'realm_id' => $this->realm->id,
            'name' => 'user',
        ]);

        $this->user->directRoles()->attach([$adminRole->id, $userRole->id]);

        $token = $this->jwtService->createAccessToken($this->user, $this->realm, $this->client->client_id, ['openid']);
        
        $parser = new Parser(new JoseEncoder());
        $parsedToken = $parser->parse($token);
        $claims = $parsedToken->claims()->all();

        $this->assertArrayHasKey('realm_access', $claims);
        $this->assertArrayHasKey('roles', $claims['realm_access']);
        $this->assertContains('admin', $claims['realm_access']['roles']);
        $this->assertContains('user', $claims['realm_access']['roles']);
    }

    public function test_access_token_includes_client_roles(): void
    {
        $viewerRole = Role::create([
            'realm_id' => $this->realm->id,
            'client_id' => $this->client->id,
            'name' => 'viewer',
        ]);

        $editorRole = Role::create([
            'realm_id' => $this->realm->id,
            'client_id' => $this->client->id,
            'name' => 'editor',
        ]);

        $this->user->directRoles()->attach([$viewerRole->id, $editorRole->id]);

        $token = $this->jwtService->createAccessToken($this->user, $this->realm, $this->client->client_id, ['openid']);
        
        $parser = new Parser(new JoseEncoder());
        $parsedToken = $parser->parse($token);
        $claims = $parsedToken->claims()->all();

        $this->assertArrayHasKey('resource_access', $claims);
        $this->assertArrayHasKey($this->client->client_id, $claims['resource_access']);
        $this->assertArrayHasKey('roles', $claims['resource_access'][$this->client->client_id]);
        $this->assertContains('viewer', $claims['resource_access'][$this->client->client_id]['roles']);
        $this->assertContains('editor', $claims['resource_access'][$this->client->client_id]['roles']);
    }

    public function test_id_token_includes_realm_roles(): void
    {
        $adminRole = Role::create([
            'realm_id' => $this->realm->id,
            'name' => 'admin',
        ]);

        $this->user->directRoles()->attach($adminRole->id);

        $token = $this->jwtService->createIdToken($this->user, $this->realm, $this->client->client_id, 'test-nonce');
        
        $parser = new Parser(new JoseEncoder());
        $parsedToken = $parser->parse($token);
        $claims = $parsedToken->claims()->all();

        $this->assertArrayHasKey('realm_access', $claims);
        $this->assertArrayHasKey('roles', $claims['realm_access']);
        $this->assertContains('admin', $claims['realm_access']['roles']);
    }

    public function test_user_inherits_roles_from_groups_in_token(): void
    {
        $adminRole = Role::create([
            'realm_id' => $this->realm->id,
            'name' => 'admin',
        ]);

        $group = Group::create([
            'realm_id' => $this->realm->id,
            'name' => 'Admins',
            'path' => '/Admins',
        ]);

        $group->roles()->attach($adminRole->id);
        $this->user->groups()->attach($group->id);

        $token = $this->jwtService->createAccessToken($this->user, $this->realm, $this->client->client_id, ['openid']);
        
        $parser = new Parser(new JoseEncoder());
        $parsedToken = $parser->parse($token);
        $claims = $parsedToken->claims()->all();

        $this->assertArrayHasKey('realm_access', $claims);
        $this->assertContains('admin', $claims['realm_access']['roles']);
    }

    public function test_composite_roles_expand_in_token(): void
    {
        $parentRole = Role::create([
            'realm_id' => $this->realm->id,
            'name' => 'super-admin',
            'composite' => true,
        ]);

        $childRole1 = Role::create([
            'realm_id' => $this->realm->id,
            'name' => 'admin',
        ]);

        $childRole2 = Role::create([
            'realm_id' => $this->realm->id,
            'name' => 'moderator',
        ]);

        $parentRole->childRoles()->attach([$childRole1->id, $childRole2->id]);
        $this->user->directRoles()->attach($parentRole->id);

        $token = $this->jwtService->createAccessToken($this->user, $this->realm, $this->client->client_id, ['openid']);
        
        $parser = new Parser(new JoseEncoder());
        $parsedToken = $parser->parse($token);
        $claims = $parsedToken->claims()->all();

        $this->assertArrayHasKey('realm_access', $claims);
        $this->assertContains('super-admin', $claims['realm_access']['roles']);
        $this->assertContains('admin', $claims['realm_access']['roles']);
        $this->assertContains('moderator', $claims['realm_access']['roles']);
    }

    public function test_token_structure_matches_keycloak_format(): void
    {
        $realmRole = Role::create([
            'realm_id' => $this->realm->id,
            'name' => 'user',
        ]);

        $clientRole = Role::create([
            'realm_id' => $this->realm->id,
            'client_id' => $this->client->id,
            'name' => 'viewer',
        ]);

        $this->user->directRoles()->attach([$realmRole->id, $clientRole->id]);

        $token = $this->jwtService->createAccessToken($this->user, $this->realm, $this->client->client_id, ['openid']);
        
        $parser = new Parser(new JoseEncoder());
        $parsedToken = $parser->parse($token);
        $claims = $parsedToken->claims()->all();

        // Verify Keycloak-compatible structure
        $this->assertIsArray($claims['realm_access']);
        $this->assertIsArray($claims['realm_access']['roles']);
        
        $this->assertIsArray($claims['resource_access']);
        $this->assertIsArray($claims['resource_access'][$this->client->client_id]);
        $this->assertIsArray($claims['resource_access'][$this->client->client_id]['roles']);
    }

    public function test_token_without_roles_omits_claims(): void
    {
        $token = $this->jwtService->createAccessToken($this->user, $this->realm, $this->client->client_id, ['openid']);
        
        $parser = new Parser(new JoseEncoder());
        $parsedToken = $parser->parse($token);
        $claims = $parsedToken->claims()->all();

        // When no roles are assigned, realm_access and resource_access should be omitted
        $this->assertArrayNotHasKey('realm_access', $claims);
        $this->assertArrayNotHasKey('resource_access', $claims);
    }
}
