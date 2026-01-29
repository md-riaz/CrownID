<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Realm;
use App\Models\RequiredAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RequiredActionsTest extends TestCase
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

    public function test_required_actions_block_authorization()
    {
        RequiredAction::create([
            'user_id' => $this->user->id,
            'action' => 'verify_email',
            'required' => true,
        ]);

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

        $response->assertRedirect(route('oidc.required-action', ['realm' => $this->realm->name]));
    }

    public function test_completing_required_action_allows_flow()
    {
        $action = RequiredAction::create([
            'user_id' => $this->user->id,
            'action' => 'verify_email',
            'required' => true,
        ]);

        session([
            'oidc_pending_user_' . $this->realm->id => $this->user->id,
            'oidc_auth_request' => [
                'realm_id' => $this->realm->id,
                'client_id' => $this->client->id,
                'redirect_uri' => 'http://localhost/callback',
                'scope' => 'openid',
                'state' => 'test-state',
                'nonce' => 'test-nonce',
            ]
        ]);

        $response = $this->post("/realms/{$this->realm->name}/protocol/openid-connect/required-action", [
            'action' => 'verify_email',
        ]);

        $action->refresh();
        $this->assertNotNull($action->completed_at);
        $response->assertRedirect();
    }

    public function test_admin_can_get_user_required_actions()
    {
        RequiredAction::create([
            'user_id' => $this->user->id,
            'action' => 'verify_email',
            'required' => true,
        ]);

        RequiredAction::create([
            'user_id' => $this->user->id,
            'action' => 'update_password',
            'required' => true,
        ]);

        $response = $this->getJson("/api/admin/realms/{$this->realm->name}/users/{$this->user->id}/required-actions");

        $response->assertOk();
        $response->assertJsonCount(2);
        $response->assertJsonFragment(['action' => 'verify_email']);
        $response->assertJsonFragment(['action' => 'update_password']);
    }

    public function test_admin_can_add_required_action()
    {
        $response = $this->postJson("/api/admin/realms/{$this->realm->name}/users/{$this->user->id}/required-actions", [
            'action' => 'configure_totp',
            'required' => true,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('required_actions', [
            'user_id' => $this->user->id,
            'action' => 'configure_totp',
            'required' => true,
        ]);
    }

    public function test_admin_can_remove_required_action()
    {
        RequiredAction::create([
            'user_id' => $this->user->id,
            'action' => 'verify_email',
            'required' => true,
        ]);

        $response = $this->deleteJson("/api/admin/realms/{$this->realm->name}/users/{$this->user->id}/required-actions/verify_email");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('required_actions', [
            'user_id' => $this->user->id,
            'action' => 'verify_email',
        ]);
    }

    public function test_multiple_required_actions_must_all_be_completed()
    {
        RequiredAction::create([
            'user_id' => $this->user->id,
            'action' => 'verify_email',
            'required' => true,
        ]);

        RequiredAction::create([
            'user_id' => $this->user->id,
            'action' => 'update_password',
            'required' => true,
        ]);

        session([
            'oidc_pending_user_' . $this->realm->id => $this->user->id,
            'oidc_auth_request' => [
                'realm_id' => $this->realm->id,
                'client_id' => $this->client->id,
                'redirect_uri' => 'http://localhost/callback',
                'scope' => 'openid',
                'state' => 'test-state',
                'nonce' => 'test-nonce',
            ]
        ]);

        $response = $this->post("/realms/{$this->realm->name}/protocol/openid-connect/required-action", [
            'action' => 'verify_email',
        ]);

        $response->assertRedirect(route('oidc.required-action', ['realm' => $this->realm->name]));

        $response = $this->post("/realms/{$this->realm->name}/protocol/openid-connect/required-action", [
            'action' => 'update_password',
        ]);

        $response->assertRedirect();
        $this->assertStringContainsString('code=', $response->headers->get('Location'));
    }

    public function test_completed_actions_are_marked_with_timestamp()
    {
        $action = RequiredAction::create([
            'user_id' => $this->user->id,
            'action' => 'verify_email',
            'required' => true,
        ]);

        $this->assertNull($action->completed_at);
        $this->assertFalse($action->isCompleted());

        $action->complete();

        $this->assertNotNull($action->completed_at);
        $this->assertTrue($action->isCompleted());
    }
}
