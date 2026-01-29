# OIDC Endpoints - Testing Guide

This document describes how to test the OIDC endpoints implemented in CrownID.

## Setup

1. Run migrations and seed test data:
```bash
php artisan migrate:fresh --seeder=OidcTestSeeder
```

This creates:
- Realm: `master`
- User: `admin@crownid.local` / Password: `admin`
- Client: `test-client` / Secret: `test-secret`
- Redirect URIs: `http://localhost:3000/callback`, `http://localhost:8080/callback`

2. Start the development server:
```bash
php artisan serve
```

## Endpoints

### 1. Discovery Endpoint

**GET** `/realms/{realm}/.well-known/openid-configuration`

```bash
curl http://localhost:8000/realms/master/.well-known/openid-configuration | jq
```

Returns:
```json
{
  "issuer": "http://localhost/realms/master",
  "authorization_endpoint": "http://localhost/realms/master/protocol/openid-connect/auth",
  "token_endpoint": "http://localhost/realms/master/protocol/openid-connect/token",
  "userinfo_endpoint": "http://localhost/realms/master/protocol/openid-connect/userinfo",
  "end_session_endpoint": "http://localhost/realms/master/protocol/openid-connect/logout",
  "jwks_uri": "http://localhost/realms/master/protocol/openid-connect/certs",
  "response_types_supported": ["code"],
  "subject_types_supported": ["public"],
  "id_token_signing_alg_values_supported": ["RS256"]
}
```

### 2. JWKS Endpoint

**GET** `/realms/{realm}/protocol/openid-connect/certs`

```bash
curl http://localhost:8000/realms/master/protocol/openid-connect/certs | jq
```

Returns public keys in JWKS format with `kid`, `kty`, `alg`, `n`, `e`.

### 3. Authorization Endpoint

**GET** `/realms/{realm}/protocol/openid-connect/auth`

Visit in browser:
```
http://localhost:8000/realms/master/protocol/openid-connect/auth?response_type=code&client_id=test-client&redirect_uri=http://localhost:3000/callback&scope=openid%20profile%20email&state=abc123&nonce=xyz789
```

This will:
1. Show login page if no SSO session exists
2. After login with `admin@crownid.local` / `admin`, redirect to:
   ```
   http://localhost:3000/callback?code={authorization_code}&state=abc123
   ```

### 4. Token Endpoint

**POST** `/realms/{realm}/protocol/openid-connect/token`

Exchange authorization code for tokens:

```bash
curl -X POST http://localhost:8000/realms/master/protocol/openid-connect/token \
  -d "grant_type=authorization_code" \
  -d "code={authorization_code}" \
  -d "redirect_uri=http://localhost:3000/callback" \
  -d "client_id=test-client" \
  -d "client_secret=test-secret"
```

Or with Basic Auth:
```bash
curl -X POST http://localhost:8000/realms/master/protocol/openid-connect/token \
  -u "test-client:test-secret" \
  -d "grant_type=authorization_code" \
  -d "code={authorization_code}" \
  -d "redirect_uri=http://localhost:3000/callback"
```

Returns:
```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImtpZCI6ImRlZmF1bHQta2lkLSJ9...",
  "token_type": "bearer",
  "expires_in": 300,
  "refresh_token": "...",
  "id_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImtpZCI6ImRlZmF1bHQta2lkLSJ9...",
  "scope": "openid profile email"
}
```

### 5. Userinfo Endpoint

**GET** `/realms/{realm}/protocol/openid-connect/userinfo`

```bash
curl http://localhost:8000/realms/master/protocol/openid-connect/userinfo \
  -H "Authorization: Bearer {access_token}"
```

Returns:
```json
{
  "sub": "1",
  "preferred_username": "admin",
  "email": "admin@crownid.local",
  "email_verified": true,
  "name": "Admin User"
}
```

### 6. Logout Endpoint

**GET** `/realms/{realm}/protocol/openid-connect/logout`

```bash
curl http://localhost:8000/realms/master/protocol/openid-connect/logout?post_logout_redirect_uri=http://localhost:3000
```

Clears SSO session and redirects to `post_logout_redirect_uri`.

## Full Authorization Code Flow Test

### Step 1: Get authorization code
Visit in browser:
```
http://localhost:8000/realms/master/protocol/openid-connect/auth?response_type=code&client_id=test-client&redirect_uri=http://localhost:3000/callback&scope=openid%20profile%20email&state=abc123&nonce=xyz789
```

Login with:
- Username: `admin@crownid.local`
- Password: `admin`

You'll be redirected to:
```
http://localhost:3000/callback?code=ABC123XYZ&state=abc123
```

Copy the `code` parameter value.

### Step 2: Exchange code for tokens
```bash
CODE="paste_code_here"
curl -X POST http://localhost:8000/realms/master/protocol/openid-connect/token \
  -d "grant_type=authorization_code" \
  -d "code=$CODE" \
  -d "redirect_uri=http://localhost:3000/callback" \
  -d "client_id=test-client" \
  -d "client_secret=test-secret" | jq
```

### Step 3: Use access token
```bash
ACCESS_TOKEN="paste_access_token_here"
curl http://localhost:8000/realms/master/protocol/openid-connect/userinfo \
  -H "Authorization: Bearer $ACCESS_TOKEN" | jq
```

### Step 4: Decode ID token
Visit [jwt.io](https://jwt.io) and paste the `id_token` to decode it.

## Running Tests

```bash
php artisan test --filter=OidcEndpointsTest
```

All 10 tests should pass:
- ✓ discovery endpoint returns correct structure
- ✓ jwks endpoint returns public keys
- ✓ authorization endpoint shows login page
- ✓ authorization endpoint validates required parameters
- ✓ authorization endpoint rejects invalid scope
- ✓ login endpoint authenticates user and redirects with code
- ✓ token endpoint exchanges code for tokens
- ✓ userinfo endpoint requires authentication
- ✓ logout endpoint clears session
- ✓ realm must exist and be enabled

## Key Features

- **RS256 JWT Signing**: Uses Passport's RSA key pair
- **SSO Session**: Redirects without login if session exists
- **Keycloak-compatible**: Follows Keycloak endpoint structure and response format
- **Standards Compliant**: Implements OpenID Connect Core 1.0
- **Error Handling**: Proper error responses for invalid requests
- **Security**: Client authentication via Basic Auth or POST parameters
