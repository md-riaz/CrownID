<?php

namespace Tests\Feature;

use App\Models\AuditEvent;
use App\Models\Client;
use App\Models\Realm;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    protected AuditService $auditService;
    protected Realm $realm;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->auditService = new AuditService();
        
        $this->realm = Realm::create([
            'name' => 'test-realm',
            'display_name' => 'Test Realm',
            'enabled' => true,
        ]);

        $this->user = User::create([
            'realm_id' => $this->realm->id,
            'username' => 'testuser',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);
    }

    public function test_login_success_event_is_logged()
    {
        $this->auditService->logLoginSuccess($this->user->id, $this->realm->id);
        
        $this->assertDatabaseHas('audit_events', [
            'event_type' => 'LOGIN_SUCCESS',
            'realm_id' => $this->realm->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_login_failed_event_is_logged()
    {
        $this->auditService->logLoginFailed($this->realm->id, [
            'username' => 'testuser',
            'reason' => 'invalid_credentials'
        ]);
        
        $event = AuditEvent::where('event_type', 'LOGIN_FAILED')->first();
        
        $this->assertNotNull($event);
        $this->assertEquals($this->realm->id, $event->realm_id);
        $this->assertEquals('invalid_credentials', $event->details['reason']);
    }

    public function test_logout_event_is_logged()
    {
        $this->auditService->logLogout($this->user->id, $this->realm->id);
        
        $this->assertDatabaseHas('audit_events', [
            'event_type' => 'LOGOUT',
            'realm_id' => $this->realm->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_mfa_enabled_event_is_logged()
    {
        $this->auditService->logMfaEnabled($this->user->id, $this->realm->id);
        
        $this->assertDatabaseHas('audit_events', [
            'event_type' => 'MFA_ENABLED',
            'realm_id' => $this->realm->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_password_changed_event_is_logged()
    {
        $this->auditService->logPasswordChanged($this->user->id, $this->realm->id);
        
        $this->assertDatabaseHas('audit_events', [
            'event_type' => 'PASSWORD_CHANGED',
            'realm_id' => $this->realm->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_account_locked_event_is_logged()
    {
        $this->auditService->logAccountLocked($this->user->id, $this->realm->id);
        
        $this->assertDatabaseHas('audit_events', [
            'event_type' => 'ACCOUNT_LOCKED',
            'realm_id' => $this->realm->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_audit_log_api_returns_events()
    {
        $this->auditService->logLoginSuccess($this->user->id, $this->realm->id);
        $this->auditService->logLogout($this->user->id, $this->realm->id);
        
        $response = $this->getJson("/api/admin/realms/{$this->realm->name}/events");
        
        $response->assertOk();
        $response->assertJsonStructure([
            'events',
            'total',
            'per_page',
            'current_page',
        ]);
        
        $this->assertGreaterThanOrEqual(2, $response->json('total'));
    }

    public function test_audit_log_api_filters_by_event_type()
    {
        $this->auditService->logLoginSuccess($this->user->id, $this->realm->id);
        $this->auditService->logLogout($this->user->id, $this->realm->id);
        
        $response = $this->getJson("/api/admin/realms/{$this->realm->name}/events?event_type=LOGIN_SUCCESS");
        
        $response->assertOk();
        $events = $response->json('events');
        
        foreach ($events as $event) {
            $this->assertEquals('LOGIN_SUCCESS', $event['event_type']);
        }
    }

    public function test_audit_log_api_filters_by_user()
    {
        $otherUser = User::create([
            'realm_id' => $this->realm->id,
            'username' => 'otheruser',
            'name' => 'Other User',
            'email' => 'other@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->auditService->logLoginSuccess($this->user->id, $this->realm->id);
        $this->auditService->logLoginSuccess($otherUser->id, $this->realm->id);
        
        $response = $this->getJson("/api/admin/realms/{$this->realm->name}/events?user_id={$this->user->id}");
        
        $response->assertOk();
        $events = $response->json('events');
        
        foreach ($events as $event) {
            $this->assertEquals($this->user->id, $event['user_id']);
        }
    }

    public function test_audit_log_captures_ip_address()
    {
        $request = \Illuminate\Http\Request::create('/test', 'GET', [], [], [], ['REMOTE_ADDR' => '127.0.0.1']);
        
        $this->auditService->logLoginSuccess($this->user->id, $this->realm->id, $request);
        
        $event = AuditEvent::where('event_type', 'LOGIN_SUCCESS')->first();
        
        $this->assertEquals('127.0.0.1', $event->ip_address);
    }
}
