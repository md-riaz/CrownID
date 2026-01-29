<?php

namespace App\Http\Controllers\Oidc;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Realm;
use App\Models\RequiredAction;
use App\Models\User;
use App\Services\AuditService;
use App\Services\JwtService;
use App\Services\RateLimitService;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OidcController extends Controller
{
    public function __construct(
        protected JwtService $jwtService,
        protected RateLimitService $rateLimitService,
        protected AuditService $auditService,
        protected TwoFactorService $twoFactorService
    ) {
    }

    public function discovery(Request $request, string $realm)
    {
        $realmModel = $request->get('realm_model');
        $baseUrl = config('app.url') . '/realms/' . $realm;

        return response()->json([
            'issuer' => $baseUrl,
            'authorization_endpoint' => $baseUrl . '/protocol/openid-connect/auth',
            'token_endpoint' => $baseUrl . '/protocol/openid-connect/token',
            'userinfo_endpoint' => $baseUrl . '/protocol/openid-connect/userinfo',
            'end_session_endpoint' => $baseUrl . '/protocol/openid-connect/logout',
            'jwks_uri' => $baseUrl . '/protocol/openid-connect/certs',
            'response_types_supported' => ['code'],
            'subject_types_supported' => ['public'],
            'id_token_signing_alg_values_supported' => ['RS256'],
            'grant_types_supported' => ['authorization_code'],
            'scopes_supported' => ['openid', 'profile', 'email'],
            'token_endpoint_auth_methods_supported' => ['client_secret_basic', 'client_secret_post'],
            'claims_supported' => [
                'sub', 'iss', 'aud', 'exp', 'iat', 'auth_time',
                'nonce', 'email', 'email_verified', 'preferred_username', 'name'
            ],
        ]);
    }

    public function certs(Request $request, string $realm)
    {
        return response()->json($this->jwtService->getJwks());
    }

    public function authorize(Request $request, string $realm)
    {
        $request->validate([
            'response_type' => 'required|in:code',
            'client_id' => 'required|string',
            'redirect_uri' => 'required|url',
            'scope' => 'required|string',
            'state' => 'required|string',
        ]);

        $realmModel = $request->get('realm_model');
        $clientId = $request->input('client_id');
        $redirectUri = $request->input('redirect_uri');
        $scope = $request->input('scope');
        $state = $request->input('state');
        $nonce = $request->input('nonce');

        if (!str_contains($scope, 'openid')) {
            return $this->errorRedirect($redirectUri, 'invalid_scope', 'Scope must include openid', $state);
        }

        $client = Client::where('realm_id', $realmModel->id)
            ->where('client_id', $clientId)
            ->where('enabled', true)
            ->first();

        if (!$client) {
            return $this->errorRedirect($redirectUri, 'invalid_client', 'Client not found', $state);
        }

        $allowedUris = is_string($client->redirect_uris) 
            ? json_decode($client->redirect_uris, true) ?? [] 
            : $client->redirect_uris ?? [];

        if (!in_array($redirectUri, $allowedUris)) {
            abort(400, 'Invalid redirect_uri');
        }

        $sessionKey = 'oidc_realm_' . $realmModel->id;
        $userId = session($sessionKey);

        if ($userId) {
            $user = User::find($userId);
            if ($user && $user->realm_id === $realmModel->id) {
                return $this->issueAuthorizationCode($realmModel, $client, $user, $redirectUri, $scope, $state, $nonce);
            }
        }

        session([
            'oidc_auth_request' => [
                'realm_id' => $realmModel->id,
                'client_id' => $client->id,  // Store UUID for later lookup
                'oauth_client_id' => $clientId,  // Store OAuth client_id for reference
                'redirect_uri' => $redirectUri,
                'scope' => $scope,
                'state' => $state,
                'nonce' => $nonce,
            ]
        ]);

        return view('oidc.login', [
            'realm' => $realmModel,
            'client' => $client,
        ]);
    }

    public function login(Request $request, string $realm)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $realmModel = $request->get('realm_model');
        $authRequest = session('oidc_auth_request');

        if (!$authRequest || $authRequest['realm_id'] !== $realmModel->id) {
            abort(400, 'Invalid authentication request');
        }

        $user = User::where('realm_id', $realmModel->id)
            ->where(function ($query) use ($request) {
                $query->where('username', $request->input('username'))
                    ->orWhere('email', $request->input('username'));
            })
            ->first();

        // Check if account is locked before attempting password verification
        if ($user && $this->rateLimitService->isRateLimited($user)) {
            $this->auditService->logLoginFailed($realmModel->id, [
                'username' => $request->input('username'),
                'reason' => 'account_locked'
            ], $request);
            return back()->withErrors(['username' => 'Account is temporarily locked due to multiple failed login attempts']);
        }

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            if ($user) {
                $this->rateLimitService->recordLoginAttempt($user, $request->ip(), false);
                $this->rateLimitService->checkAndLockIfNeeded($user);
                $user->refresh();
                $this->auditService->logLoginFailed($realmModel->id, [
                    'username' => $request->input('username'),
                    'reason' => 'invalid_credentials'
                ], $request);
            }
            return back()->withErrors(['username' => 'Invalid credentials']);
        }

        $this->rateLimitService->recordLoginAttempt($user, $request->ip(), true);

        $pendingActions = $user->requiredActions()
            ->where('required', true)
            ->whereNull('completed_at')
            ->get();

        if ($pendingActions->isNotEmpty()) {
            session(['oidc_pending_user_' . $realmModel->id => $user->id]);
            return redirect()->route('oidc.required-action', ['realm' => $realm]);
        }

        if ($user->hasTwoFactorEnabled()) {
            session(['oidc_mfa_user_' . $realmModel->id => $user->id]);
            return redirect()->route('oidc.mfa-challenge', ['realm' => $realm]);
        }

        session(['oidc_realm_' . $realmModel->id => $user->id]);
        session()->forget('oidc_auth_request');

        $this->auditService->logLoginSuccess($user->id, $realmModel->id, $request);

        $client = Client::find($authRequest['client_id']);

        return $this->issueAuthorizationCode(
            $realmModel,
            $client,
            $user,
            $authRequest['redirect_uri'],
            $authRequest['scope'],
            $authRequest['state'],
            $authRequest['nonce'] ?? null
        );
    }

    public function token(Request $request, string $realm)
    {
        $request->validate([
            'grant_type' => 'required|in:authorization_code',
            'code' => 'required_if:grant_type,authorization_code',
            'redirect_uri' => 'required_if:grant_type,authorization_code',
        ]);

        $realmModel = $request->get('realm_model');
        $grantType = $request->input('grant_type');

        if ($grantType === 'authorization_code') {
            return $this->handleAuthorizationCodeGrant($request, $realmModel);
        }

        return response()->json(['error' => 'unsupported_grant_type'], 400);
    }

    public function userinfo(Request $request, string $realm)
    {
        $realmModel = $request->get('realm_model');
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['error' => 'invalid_token'], 401);
        }

        $token = substr($authHeader, 7);
        $parsed = $this->jwtService->verifyToken($token);

        if (!$parsed) {
            return response()->json(['error' => 'invalid_token'], 401);
        }

        $userId = $parsed->claims()->get('sub');
        $user = User::where('id', $userId)
            ->where('realm_id', $realmModel->id)
            ->first();

        if (!$user) {
            return response()->json(['error' => 'invalid_token'], 401);
        }

        return response()->json([
            'sub' => (string) $user->id,
            'preferred_username' => $user->username ?? $user->email,
            'email' => $user->email,
            'email_verified' => $user->email_verified_at !== null,
            'name' => $user->name,
        ]);
    }

    public function logout(Request $request, string $realm)
    {
        $realmModel = $request->get('realm_model');
        $postLogoutRedirectUri = $request->input('post_logout_redirect_uri');

        $userId = session('oidc_realm_' . $realmModel->id);
        if ($userId) {
            $this->auditService->logLogout($userId, $realmModel->id, $request);
        }

        session()->forget('oidc_realm_' . $realmModel->id);
        session()->forget('oidc_auth_request');

        if ($postLogoutRedirectUri) {
            return redirect($postLogoutRedirectUri);
        }

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function showMfaChallenge(Request $request, string $realm)
    {
        $realmModel = $request->get('realm_model');
        $userId = session('oidc_mfa_user_' . $realmModel->id);

        if (!$userId) {
            return redirect()->route('oidc.authorize', ['realm' => $realm]);
        }

        $user = User::find($userId);
        return view('oidc.mfa-challenge', [
            'realm' => $realmModel,
            'user' => $user,
        ]);
    }

    public function verifyMfa(Request $request, string $realm)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $realmModel = $request->get('realm_model');
        $userId = session('oidc_mfa_user_' . $realmModel->id);

        if (!$userId) {
            abort(400, 'Invalid MFA request');
        }

        $user = User::find($userId);
        $authRequest = session('oidc_auth_request');

        if (!$user || !$authRequest) {
            abort(400, 'Invalid MFA request');
        }

        $code = $request->input('code');
        $isValid = $user->verifyTwoFactorCode($code);

        if (!$isValid) {
            $isValid = $this->twoFactorService->verifyBackupCode($user, $code);
        }

        if (!$isValid) {
            return back()->withErrors(['code' => 'Invalid verification code']);
        }

        session()->forget('oidc_mfa_user_' . $realmModel->id);
        session(['oidc_realm_' . $realmModel->id => $user->id]);
        session()->forget('oidc_auth_request');

        $this->auditService->logLoginSuccess($user->id, $realmModel->id, $request);

        $client = Client::find($authRequest['client_id']);

        return $this->issueAuthorizationCode(
            $realmModel,
            $client,
            $user,
            $authRequest['redirect_uri'],
            $authRequest['scope'],
            $authRequest['state'],
            $authRequest['nonce'] ?? null
        );
    }

    public function showRequiredAction(Request $request, string $realm)
    {
        $realmModel = $request->get('realm_model');
        $userId = session('oidc_pending_user_' . $realmModel->id);

        if (!$userId) {
            return redirect()->route('oidc.authorize', ['realm' => $realm]);
        }

        $user = User::find($userId);
        $actions = $user->requiredActions()
            ->where('required', true)
            ->whereNull('completed_at')
            ->get();

        return view('oidc.required-action', [
            'realm' => $realmModel,
            'user' => $user,
            'actions' => $actions,
        ]);
    }

    public function completeRequiredAction(Request $request, string $realm)
    {
        $request->validate([
            'action' => 'required|in:verify_email,update_password,configure_totp',
        ]);

        $realmModel = $request->get('realm_model');
        $userId = session('oidc_pending_user_' . $realmModel->id);

        if (!$userId) {
            abort(400, 'Invalid required action request');
        }

        $user = User::find($userId);
        $action = $user->requiredActions()
            ->where('action', $request->input('action'))
            ->first();

        if ($action) {
            $action->complete();
        }

        $remainingActions = $user->requiredActions()
            ->where('required', true)
            ->whereNull('completed_at')
            ->count();

        if ($remainingActions > 0) {
            return redirect()->route('oidc.required-action', ['realm' => $realm]);
        }

        $authRequest = session('oidc_auth_request');

        if ($user->hasTwoFactorEnabled()) {
            session()->forget('oidc_pending_user_' . $realmModel->id);
            session(['oidc_mfa_user_' . $realmModel->id => $user->id]);
            return redirect()->route('oidc.mfa-challenge', ['realm' => $realm]);
        }

        session()->forget('oidc_pending_user_' . $realmModel->id);
        session(['oidc_realm_' . $realmModel->id => $user->id]);
        session()->forget('oidc_auth_request');

        $this->auditService->logLoginSuccess($user->id, $realmModel->id, $request);

        $client = Client::find($authRequest['client_id']);

        return $this->issueAuthorizationCode(
            $realmModel,
            $client,
            $user,
            $authRequest['redirect_uri'],
            $authRequest['scope'],
            $authRequest['state'],
            $authRequest['nonce'] ?? null
        );
    }

    protected function issueAuthorizationCode(Realm $realm, Client $client, User $user, string $redirectUri, string $scope, string $state, ?string $nonce)
    {
        $code = Str::random(64);
        $expiresAt = now()->addSeconds(90);

        DB::table('oauth_auth_codes')->insert([
            'id' => $code,
            'user_id' => $user->id,
            'client_id' => $client->id,
            'scopes' => json_encode(explode(' ', $scope)),
            'redirect_uri' => $redirectUri,
            'nonce' => $nonce,
            'revoked' => false,
            'expires_at' => $expiresAt,
        ]);

        $separator = str_contains($redirectUri, '?') ? '&' : '?';
        return redirect($redirectUri . $separator . http_build_query([
            'code' => $code,
            'state' => $state,
        ]));
    }

    protected function handleAuthorizationCodeGrant(Request $request, Realm $realm)
    {
        $code = $request->input('code');
        $redirectUri = $request->input('redirect_uri');

        // Load authorization code from database, not session
        $codeRecord = DB::table('oauth_auth_codes')
            ->where('id', $code)
            ->where('revoked', false)
            ->first();

        if (!$codeRecord) {
            return response()->json(['error' => 'invalid_grant', 'error_description' => 'Invalid authorization code'], 400);
        }

        if ($codeRecord->expires_at && strtotime($codeRecord->expires_at) < time()) {
            DB::table('oauth_auth_codes')->where('id', $code)->update(['revoked' => true]);
            return response()->json(['error' => 'invalid_grant', 'error_description' => 'Authorization code expired'], 400);
        }

        if ($codeRecord->redirect_uri !== $redirectUri) {
            return response()->json(['error' => 'invalid_grant', 'error_description' => 'Redirect URI mismatch'], 400);
        }

        // Get client_id from authentication (Basic Auth or POST body)
        $authenticatedClientId = $this->getClientIdFromAuth($request);
        
        if (!$authenticatedClientId || $codeRecord->client_id !== $authenticatedClientId) {
            return response()->json(['error' => 'invalid_client'], 401);
        }

        $user = User::find($codeRecord->user_id);
        $client = Client::find($authenticatedClientId);

        if (!$user || !$client) {
            return response()->json(['error' => 'invalid_grant'], 400);
        }

        // Revoke the authorization code (one-time use)
        DB::table('oauth_auth_codes')->where('id', $code)->update(['revoked' => true]);

        $scopes = json_decode($codeRecord->scopes, true) ?? [];
        $accessToken = $this->jwtService->createAccessToken($user, $realm, $client->client_id, $scopes);
        $idToken = $this->jwtService->createIdToken($user, $realm, $client->client_id, $codeRecord->nonce);
        $refreshToken = Str::random(64);

        $expiresIn = $realm->access_token_lifespan ?? 300;

        return response()->json([
            'access_token' => $accessToken,
            'token_type' => 'bearer',
            'expires_in' => $expiresIn,
            'refresh_token' => $refreshToken,
            'id_token' => $idToken,
            'scope' => implode(' ', $scopes),
        ]);
    }

    protected function getClientIdFromAuth(Request $request): ?string
    {
        $authHeader = $request->header('Authorization');
        
        if ($authHeader && str_starts_with($authHeader, 'Basic ')) {
            $credentials = base64_decode(substr($authHeader, 6));
            [$oauthClientId, $clientSecret] = explode(':', $credentials, 2);
            
            $client = Client::where('client_id', $oauthClientId)->first();
            if ($client && hash_equals($client->secret ?? '', $clientSecret)) {
                return $client->id;  // Return UUID primary key
            }
        }

        $oauthClientId = $request->input('client_id');
        $clientSecret = $request->input('client_secret');
        
        if ($oauthClientId && $clientSecret) {
            $client = Client::where('client_id', $oauthClientId)->first();
            if ($client && hash_equals($client->secret ?? '', $clientSecret)) {
                return $client->id;  // Return UUID primary key
            }
        }

        return null;
    }

    protected function errorRedirect(string $redirectUri, string $error, string $description, ?string $state = null)
    {
        $separator = str_contains($redirectUri, '?') ? '&' : '?';
        $params = [
            'error' => $error,
            'error_description' => $description,
        ];
        
        if ($state) {
            $params['state'] = $state;
        }
        
        return redirect($redirectUri . $separator . http_build_query($params));
    }
}
