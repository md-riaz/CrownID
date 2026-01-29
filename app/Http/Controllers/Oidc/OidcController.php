<?php

namespace App\Http\Controllers\Oidc;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Realm;
use App\Models\User;
use App\Services\JwtService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OidcController extends Controller
{
    public function __construct(protected JwtService $jwtService)
    {
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
            'grant_types_supported' => ['authorization_code', 'refresh_token'],
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
            ->where('id', $clientId)
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
                'client_id' => $clientId,
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

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            return back()->withErrors(['username' => 'Invalid credentials']);
        }

        session(['oidc_realm_' . $realmModel->id => $user->id]);
        session()->forget('oidc_auth_request');

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
            'grant_type' => 'required|in:authorization_code,refresh_token',
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

        session()->forget('oidc_realm_' . $realmModel->id);
        session()->forget('oidc_auth_request');

        if ($postLogoutRedirectUri) {
            return redirect($postLogoutRedirectUri);
        }

        return response()->json(['message' => 'Logged out successfully']);
    }

    protected function issueAuthorizationCode(Realm $realm, Client $client, User $user, string $redirectUri, string $scope, string $state, ?string $nonce)
    {
        $code = Str::random(64);
        $expiresAt = now()->addMinutes(5);

        DB::table('oauth_auth_codes')->insert([
            'id' => $code,
            'user_id' => $user->id,
            'client_id' => $client->id,
            'scopes' => json_encode(explode(' ', $scope)),
            'revoked' => false,
            'expires_at' => $expiresAt,
        ]);

        session([
            'oidc_code_' . $code => [
                'user_id' => $user->id,
                'client_id' => $client->id,
                'redirect_uri' => $redirectUri,
                'scope' => $scope,
                'nonce' => $nonce,
                'expires_at' => $expiresAt->timestamp,
            ]
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

        $codeData = session('oidc_code_' . $code);

        if (!$codeData) {
            return response()->json(['error' => 'invalid_grant', 'error_description' => 'Invalid authorization code'], 400);
        }

        if ($codeData['expires_at'] < time()) {
            session()->forget('oidc_code_' . $code);
            return response()->json(['error' => 'invalid_grant', 'error_description' => 'Authorization code expired'], 400);
        }

        if ($codeData['redirect_uri'] !== $redirectUri) {
            return response()->json(['error' => 'invalid_grant', 'error_description' => 'Redirect URI mismatch'], 400);
        }

        $clientId = $this->getClientIdFromAuth($request);
        
        if (!$clientId || $codeData['client_id'] !== $clientId) {
            return response()->json(['error' => 'invalid_client'], 401);
        }

        $user = User::find($codeData['user_id']);
        $client = Client::find($clientId);

        if (!$user || !$client) {
            return response()->json(['error' => 'invalid_grant'], 400);
        }

        session()->forget('oidc_code_' . $code);

        $scopes = explode(' ', $codeData['scope']);
        $accessToken = $this->jwtService->createAccessToken($user, $realm, $clientId, $scopes);
        $idToken = $this->jwtService->createIdToken($user, $realm, $clientId, $codeData['nonce'] ?? null);
        $refreshToken = Str::random(64);

        $expiresIn = $realm->access_token_lifespan ?? 300;

        return response()->json([
            'access_token' => $accessToken,
            'token_type' => 'bearer',
            'expires_in' => $expiresIn,
            'refresh_token' => $refreshToken,
            'id_token' => $idToken,
            'scope' => $codeData['scope'],
        ]);
    }

    protected function getClientIdFromAuth(Request $request): ?string
    {
        $authHeader = $request->header('Authorization');
        
        if ($authHeader && str_starts_with($authHeader, 'Basic ')) {
            $credentials = base64_decode(substr($authHeader, 6));
            [$clientId, $clientSecret] = explode(':', $credentials, 2);
            
            $client = Client::find($clientId);
            if ($client && hash_equals($client->secret ?? '', $clientSecret)) {
                return $clientId;
            }
        }

        $clientId = $request->input('client_id');
        $clientSecret = $request->input('client_secret');
        
        if ($clientId && $clientSecret) {
            $client = Client::find($clientId);
            if ($client && hash_equals($client->secret ?? '', $clientSecret)) {
                return $clientId;
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
