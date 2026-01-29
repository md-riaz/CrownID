# CrownID - Keycloak-Compatible IAM Server

CrownID is a Keycloak-compatible Identity and Access Management (IAM) server built with Laravel. It provides OpenID Connect (OIDC) authentication and authorization services with a focus on external API compatibility with Keycloak.

## Features

### Phase 0 - OIDC Core (✅ Complete)

- ✅ **OpenID Connect Provider** with all core endpoints:
  - Discovery (`.well-known/openid-configuration`)
  - JWKS (public key distribution)
  - Authorization (OAuth 2.0 authorization code flow)
  - Token (code to token exchange)
  - Userinfo (user claims)
  - Logout (session termination)

- ✅ **JWT Token Generation** with RS256 signing
- ✅ **SSO Session Management**
- ✅ **Multi-Realm Support**
- ✅ **Client Management** (confidential/public)
- ✅ **User Authentication** with password hashing

### Phase 2 - Admin REST API (✅ Complete)

- ✅ **Realm Management**:
  - GET /api/admin/realms - List all realms
  - GET /api/admin/realms/{realm} - Get realm details
  - POST /api/admin/realms - Create realm
  - PUT /api/admin/realms/{realm} - Update realm
  - DELETE /api/admin/realms/{realm} - Delete realm

- ✅ **User Management**:
  - GET /api/admin/realms/{realm}/users - List users (with pagination & search)
  - POST /api/admin/realms/{realm}/users - Create user
  - GET /api/admin/realms/{realm}/users/{id} - Get user details
  - PUT /api/admin/realms/{realm}/users/{id} - Update user
  - DELETE /api/admin/realms/{realm}/users/{id} - Delete user

- ✅ **Client Management**:
  - GET /api/admin/realms/{realm}/clients - List clients
  - POST /api/admin/realms/{realm}/clients - Create client
  - GET /api/admin/realms/{realm}/clients/{id} - Get client details
  - PUT /api/admin/realms/{realm}/clients/{id} - Update client
  - DELETE /api/admin/realms/{realm}/clients/{id} - Delete client

- ✅ **Keycloak-Compatible JSON Representations**

## 📸 Complete UI Pages & API Documentation

This section provides a comprehensive visual tour of **ALL pages and functionalities** in CrownID with detailed descriptions.

### 🎨 User Interface Pages

#### 1. 🔐 Login Page  
**URL**: `/realms/{realm}/protocol/openid-connect/auth`

**Purpose**: Primary authentication entry point for OAuth 2.0 Authorization Code flow.

**Visual Design**:
- Modern purple-to-violet gradient background (#667eea → #764ba2)
- Clean white authentication card (max-width: 400px, responsive)
- Professional typography with system fonts
- Smooth animations and focus states

**Form Elements**:
- **Realm Name Display** - Shows which realm (tenant) user is logging into
- **Client Context Box** - "Signing in to {Client Application Name}"
- **Username/Email Field** - Single input accepts both
- **Password Field** - Secure input with autocomplete support
- **Sign In Button** - Full-width gradient button with hover effects
- **Error Messages** - Red alert box for authentication failures

**Page States**:
- ✨ Clean/empty state
- 📝 Form filled, ready to submit
- ⏳ Loading/processing state
- ❌ Error: Invalid credentials
- 🔒 Error: Account locked (after max failed login attempts)
- ⚠️ Error: Account disabled

**CSS Features**:
- Border-radius: 12px on card, 6px on inputs
- Box-shadow: `0 20px 60px rgba(0,0,0,0.3)`
- Transitions: All interactions smoothly animated
- Responsive: Mobile-first design, adapts to all screen sizes

**Keycloak Comparison**:
- ✅ **URL**: Identical structure `/realms/{realm}/protocol/openid-connect/auth`
- ✅ **Flow**: Same OAuth 2.0 authorization code flow
- ✅ **SSO**: Session management behavior identical
- 🎨 **Design**: Modern gradient UI vs Keycloak's default Bootstrap theme
- ⚙️ **Customization**: Blade templates vs Keycloak FreeMarker templates

---

#### 2. 🔢 Two-Factor Authentication (MFA) Page
**URL**: `/realms/{realm}/protocol/openid-connect/mfa`

**Purpose**: TOTP (Time-based One-Time Password) verification after successful password authentication.

**Visual Design**:
- Consistent gradient background (brand continuity)
- Focused, minimal interface for code entry
- Large, centered input with letter-spacing

**Form Elements**:
- **Title**: "Two-Factor Authentication"
- **Subtitle**: "Enter your verification code"
- **6-8 Digit Code Input**: 
  - Font-size: 18px for easy reading
  - Letter-spacing: 8px for visual separation
  - Text-align: center
  - Placeholder: "000000"
- **Verify Button**: Gradient styling matching brand
- **Help Text**: "Enter the 6-digit code from your authenticator app or use a backup code if needed"

**Supported Authentication Methods**:
- ✅ **Google Authenticator** (Android/iOS)
- ✅ **Microsoft Authenticator** (Android/iOS/Windows)
- ✅ **Authy** (Multi-device)
- ✅ **1Password** (Built-in TOTP)
- ✅ **Any RFC 6238 TOTP app**
- ✅ **Backup Codes** (8 single-use codes per user)

**Page States**:
- 🆕 Clean state, awaiting code entry
- 🔢 Code entered (6 digits visible)
- ⏳ Verifying code...
- ❌ Invalid code error (code doesn't match)
- ⏱️ Expired code error (time window passed)
- ✅ Success (redirects to app or required actions)

**Security Features**:
- ⏱️ **Time-based codes** (30-second window, RFC 6238)
- 🔐 **Secrets encrypted** at rest (Laravel encryption)
- 🎫 **Backup codes hashed** (bcrypt)
- 🚫 **Rate limiting** (prevents brute force)
- 📱 **QR code setup** (scan to configure)

**Keycloak Comparison**:
- ✅ **Same TOTP algorithm** (RFC 6238 standard)
- ✅ **Backup codes** identical behavior
- ✅ **Flow integration** matches Keycloak
- 🎨 **Modern minimalist UI** vs Keycloak's form-heavy design

---

#### 3. ⚠️ Required Actions Page
**URL**: `/realms/{realm}/protocol/openid-connect/required-action`

**Purpose**: Forces users to complete mandatory actions before accessing applications. Blocks authentication flow completely.

**Visual Design**:
- Wider card (500px max-width) for action list
- Card-based layout with individual action items
- Each action in light gray container (#f8f9fa)
- Clear visual hierarchy and spacing

**Action Types**:

##### 📧 Verify Email
- **Description**: "You need to verify your email address before continuing"
- **Flow**: Sends email → User clicks link → Email verified
- **Use Case**: New accounts, email changes, security verification
- **Cannot Proceed Until**: Email is verified

##### 🔑 Update Password
- **Description**: "Your password needs to be updated for security reasons"
- **Flow**: Shows password change form → User enters new password → Password updated
- **Use Cases**:
  - Expired password (policy-based)
  - Compromised credentials
  - First-time login password change
  - Admin-forced password reset

##### 🛡️ Configure TOTP (Two-Factor Authentication)
- **Description**: "Set up two-factor authentication to secure your account"
- **Flow**: 
  1. QR code displayed
  2. User scans with authenticator app
  3. User enters test code to verify
  4. 8 backup codes generated and shown
  5. User confirms they saved backup codes
- **Use Case**: Enforced MFA policy, high-security accounts

**Layout & Interaction**:
- 📋 **Action List**: Vertical stack of cards
- **Each Card Contains**:
  - Bold title (16px, #333)
  - Gray description text (13px, #666)
  - "Complete" button (right-aligned, gradient)
- 🔄 **Dynamic**: Actions disappear when completed
- ⚠️ **Blocking Message**: "You must complete all required actions to continue"

**Page States**:
- 📋 Single action pending
- 📋📋📋 Multiple actions pending
- ⏳ Action in progress (loading state)
- ✅ Action just completed (smooth removal animation)
- 🎉 All actions completed (auto-redirect)

**Behavior**:
- ❌ **Cannot skip** - No dismiss or close button
- 🔒 **Completely blocks** OAuth flow until done
- ✅ **Any order** - User can complete actions in any sequence
- 🔄 **Persistent** - Remains required across login attempts
- 👮 **Admin controlled** - Admins can add/remove via API

**Keycloak Comparison**:
- ✅ **Same action types** as Keycloak
- ✅ **Identical blocking behavior**
- ✅ **Admin API compatibility** for managing actions
- ✅ **Flow integration** matches exactly
- 🎨 **Card-based modern UI** vs Keycloak's traditional list view

---

#### 4. 🏠 Welcome/Landing Page
**URL**: `/`

**Default Laravel welcome page** - Customizable for your branding and information.

---

### 🔌 API Endpoint Responses

#### 5. 🔍 OIDC Discovery Endpoint
**URL**: `GET /realms/{realm}/.well-known/openid-configuration`

**Purpose**: OpenID Connect discovery document for automatic client configuration.

**Response**:
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
  "id_token_signing_alg_values_supported": ["RS256"],
  "grant_types_supported": ["authorization_code", "refresh_token"],
  "scopes_supported": ["openid", "profile", "email"],
  "token_endpoint_auth_methods_supported": ["client_secret_basic", "client_secret_post"],
  "claims_supported": ["sub", "iss", "aud", "exp", "iat", "auth_time", "nonce",
                       "email", "email_verified", "preferred_username", "name"]
}
```

**Keycloak Comparison**: ✅ **100% Compatible** - All required OpenID Connect Discovery 1.0 fields present

---

#### 6. 🔑 JWKS Endpoint (Public Keys)
**URL**: `GET /realms/{realm}/protocol/openid-connect/certs`

**Purpose**: JSON Web Key Set for JWT signature verification.

**Response**:
```json
{
  "keys": [
    {
      "kty": "RSA",
      "use": "sig",
      "kid": "unique-key-identifier-abc123",
      "n": "modulus-base64-encoded...",
      "e": "AQAB",
      "alg": "RS256"
    }
  ]
}
```

**Usage**: Clients download public keys to verify JWT signatures offline.

---

#### 7. 🏢 Admin API - List Realms
**URL**: `GET /api/admin/realms`

**Response**:
```json
{
  "data": [
    {
      "id": "1",
      "realm": "master",
      "displayName": "Master",
      "enabled": true,
      "accessTokenLifespan": 300,
      "refreshTokenLifespan": 1800,
      "ssoSessionIdleTimeout": 1800,
      "ssoSessionMaxLifespan": 36000
    }
  ]
}
```

**Operations**: Create, read, update, delete realms with full configuration.

---

#### 8. 👥 Admin API - List Users
**URL**: `GET /api/admin/realms/{realm}/users?search=admin&first=0&max=10`

**Response**:
```json
[
  {
    "id": "1",
    "username": "admin",
    "email": "admin@crownid.local",
    "firstName": "Admin",
    "lastName": "User",
    "enabled": true,
    "emailVerified": true,
    "attributes": {},
    "createdTimestamp": 1769661020000
  }
]
```

**Features**: CRUD operations, pagination, full-text search, custom attributes, credential management.

---

#### 9. 🔐 Admin API - List Clients
**URL**: `GET /api/admin/realms/{realm}/clients`

**Response**:
```json
{
  "data": [
    {
      "id": "uuid",
      "clientId": "test-client",
      "name": "Test Application",
      "enabled": true,
      "clientAuthenticatorType": "client-secret",
      "redirectUris": ["http://localhost:8000/callback"],
      "publicClient": false
    }
  ]
}
```

**Client Types**: Confidential (with secret) and Public (no secret for SPAs).

---

#### 10. 👔 Admin API - List Roles
**URL**: `GET /api/admin/realms/{realm}/roles`

**Response**:
```json
{
  "data": [
    {
      "id": "1",
      "name": "admin",
      "description": "Administrator role",
      "composite": false,
      "clientRole": false
    }
  ]
}
```

**Features**: Realm roles, client roles, composite roles, role mappings.

---

#### 11. 👨‍👩‍👧‍👦 Admin API - List Groups
**URL**: `GET /api/admin/realms/{realm}/groups`

**Response**:
```json
{
  "data": [
    {
      "id": "1",
      "name": "Administrators",
      "path": "/Administrators",
      "subGroups": []
    }
  ]
}
```

**Features**: Hierarchical structure, role inheritance, user membership management.

---

#### 12. 🎫 Token Response
**URL**: `POST /realms/{realm}/protocol/openid-connect/token`

**Response**:
```json
{
  "access_token": "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9...",
  "token_type": "Bearer",
  "expires_in": 300,
  "refresh_token": "eyJhbGciOiJSUzI1NiJ9...",
  "id_token": "eyJhbGciOiJSUzI1NiJ9...",
  "scope": "openid profile email"
}
```

**JWT Payload (decoded)**:
```json
{
  "sub": "1",
  "email": "admin@crownid.local",
  "preferred_username": "admin",
  "realm_access": {
    "roles": ["admin", "user"]
  },
  "resource_access": {
    "test-client": {
      "roles": ["viewer", "editor"]
    }
  }
}
```

---

#### 13. 👤 Userinfo Endpoint
**URL**: `GET /realms/{realm}/protocol/openid-connect/userinfo`  
**Headers**: `Authorization: Bearer {access_token}`

**Response**:
```json
{
  "sub": "1",
  "preferred_username": "admin",
  "email": "admin@crownid.local",
  "email_verified": true,
  "name": "Admin User"
}
```

---

#### 14. 📊 Audit Events API
**URL**: `GET /api/admin/realms/{realm}/events`

**Response**:
```json
{
  "data": [
    {
      "id": "1",
      "type": "LOGIN_SUCCESS",
      "realmId": "master",
      "userId": "1",
      "ipAddress": "127.0.0.1",
      "details": {},
      "createdAt": "2026-01-29T04:30:20Z"
    }
  ]
}
```

**Event Types**: LOGIN, LOGOUT, MFA_ENABLED, PASSWORD_CHANGED, ACCOUNT_LOCKED, user/client/realm CRUD events.

---

#### 15. 📦 Realm Export/Import
**URL**: `GET /api/admin/realms/{realm}/export?includeUsers=false`

**Response**: Complete realm configuration (clients, roles, groups, users, mappings) in Keycloak-compatible JSON format.

**CLI Commands**:
```bash
php artisan crownid:export master --file=backup.json --users
php artisan crownid:import backup.json
php artisan crownid:import-directory /exports/
```

---

### 🔄 Complete Authentication Flow

```
┌─────────────┐
│   Client    │
│ Application │
└──────┬──────┘
       │ 1. Redirect to authorization endpoint with parameters
       ↓
┌──────────────────────────────────┐
│  LOGIN PAGE                      │
│  User enters username/password   │ ◄── First authentication factor
└──────┬───────────────────────────┘
       │ 2. Validate credentials
       ↓
       ├─ If MFA enabled ──→ ┌─────────────────┐
       │                      │  MFA CHALLENGE  │ ◄── TOTP verification
       │                      └────────┬────────┘
       │ ←────────────────────────────┘
       │
       ├─ If required actions ──→ ┌──────────────────┐
       │                           │ REQUIRED ACTIONS │ ◄── Must complete
       │                           └────────┬─────────┘
       │ ←──────────────────────────────────┘
       │
       │ 3. Generate authorization code (single-use, 90s expiry)
       ↓
┌──────────────────────────────────┐
│  Redirect to client callback     │
│  with code & state parameters    │
└──────┬───────────────────────────┘
       │
       ↓
┌─────────────┐
│   Client    │ 4. POST to token endpoint
│ Application │    with code + client credentials
└──────┬──────┘
       │
       ↓
┌──────────────────────────────────┐
│  TOKEN RESPONSE                  │
│  - access_token (JWT, 5min)      │
│  - id_token (JWT, identity)      │
│  - refresh_token (30min)         │
│  - token_type: Bearer            │
└──────────────────────────────────┘
```

---

### 🎨 Design System

#### Colors
- **Primary Gradient**: `#667eea` → `#764ba2`
- **Text Primary**: `#333`
- **Text Secondary**: `#666`
- **Background**: `#fff`
- **Error BG**: `#fee` / Text: `#c33`
- **Input Border**: `#ddd` / Focus: `#667eea`
- **Light BG**: `#f8f9fa`

#### Typography
- **Font Stack**: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif
- **H1**: 24px / 600 weight
- **Body**: 14px / 400 weight
- **Labels**: 14px / 500 weight
- **Buttons**: 16px / 600 weight

#### Layout
- **Card Max Width**: 400px (forms), 500px (actions)
- **Padding**: 40px (desktop), 20px (mobile)
- **Border Radius**: 6px (inputs), 12px (cards)
- **Shadow**: `0 20px 60px rgba(0,0,0,0.3)`

---

### 🧪 Testing the UI

```bash
# Start server
php artisan serve

# Visit login page (replace {client-id} with actual UUID)
http://localhost:8000/realms/master/protocol/openid-connect/auth?client_id={client-id}&redirect_uri=http://localhost:8000/callback&response_type=code&scope=openid&state=test&nonce=test

# Default credentials
Username: admin@crownid.local
Password: password
```

### Testing APIs

```bash
# Discovery
curl http://localhost:8000/realms/master/.well-known/openid-configuration | jq

# Admin API
curl http://localhost:8000/api/admin/realms | jq
curl http://localhost:8000/api/admin/realms/master/users | jq
curl http://localhost:8000/api/admin/realms/master/clients | jq
```

---

### ⚖️ Keycloak Compatibility Matrix

| Feature | CrownID | Keycloak | Status |
|---------|---------|----------|--------|
| **OIDC Core Endpoints** | ✅ All | ✅ All | ✅ 100% Compatible |
| **URL Structure** | `/realms/{realm}/protocol/...` | Same | ✅ Identical |
| **OAuth 2.0 Flows** | Authorization Code | Multiple | ✅ Core flow |
| **JWT Signing** | RS256 | RS256, HS256, etc. | ✅ RS256 |
| **Token Claims** | Standard + roles | Standard + extensions | ✅ Compatible |
| **Admin REST API** | ~40% of endpoints | 100% | ⚠️ Core features only |
| **JSON Format** | Keycloak-style | Native | ✅ Compatible |
| **Roles & Groups** | ✅ Full | ✅ Full | ✅ Compatible |
| **MFA/TOTP** | ✅ RFC 6238 | ✅ RFC 6238 | ✅ Compatible |
| **Realm Export/Import** | ✅ JSON | ✅ JSON | ✅ Compatible |
| **Admin Console UI** | ❌ API only | ✅ Full UI | ❌ Not available |
| **SAML Protocol** | ❌ | ✅ | ❌ OIDC only |
| **User Federation** | ❌ | ✅ LDAP/AD | ❌ Direct DB |
| **Identity Brokering** | ❌ | ✅ Social login | ❌ Not available |
| **Custom Themes** | ✅ Blade templates | ✅ FreeMarker | ⚠️ Different approach |

**Summary**: CrownID is **API-compatible** with Keycloak for OIDC and core admin operations. Perfect for applications needing lightweight OIDC without Java/Keycloak overhead.


### Requirements

- PHP 8.2+
- Composer
- SQLite (for development) or PostgreSQL (for production)
- Node.js & NPM

### Installation

```bash
# Clone repository
git clone https://github.com/md-riaz/CrownID.git
cd CrownID

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Generate OAuth keys
php artisan passport:keys

# Run migrations and seed test data
php artisan migrate:fresh --seeder=OidcTestSeeder

# Build assets
npm run build

# Start development server
php artisan serve
```

### Test Data

The `OidcTestSeeder` creates:
- **Realm**: `master`
- **User**: `admin@crownid.local` / Password: `admin`
- **Client**: `test-client` / Secret: `test-secret`
- **Redirect URIs**: `http://localhost:3000/callback`, `http://localhost:8080/callback`

## Testing

### Run Test Suite

```bash
php artisan test
```

### Manual OIDC Flow Testing

See [OIDC_TESTING.md](OIDC_TESTING.md) for detailed testing instructions including:
- Discovery endpoint
- Authorization code flow (step-by-step)
- Token exchange
- Userinfo endpoint
- JWT token validation

### Quick Test

```bash
# Test discovery endpoint
curl http://localhost:8000/realms/master/.well-known/openid-configuration | jq

# Test admin API - List realms
curl http://localhost:8000/api/admin/realms | jq

# Create a new user
curl -X POST http://localhost:8000/api/admin/realms/master/users \
  -H "Content-Type: application/json" \
  -d '{
    "username": "testuser",
    "email": "test@example.com",
    "firstName": "Test",
    "lastName": "User",
    "credentials": [{
      "type": "password",
      "value": "password123"
    }]
  }' | jq
```

## Development

```bash
# Run tests
composer test

# Run with hot reload and logging
composer dev
```

## Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   ├── RealmController.php    # Realm CRUD
│   │   │   ├── UserController.php     # User CRUD + search
│   │   │   └── ClientController.php   # Client CRUD
│   │   └── Oidc/
│   │       └── OidcController.php     # OIDC endpoints
│   ├── Middleware/
│   │   └── RealmExists.php            # Realm validation
│   ├── Requests/Admin/                # Form validation
│   └── Resources/                     # JSON resources
│       ├── RealmResource.php
│       ├── UserResource.php
│       └── ClientResource.php
├── Models/
│   ├── Realm.php                      # Multi-tenancy
│   ├── Client.php                     # OAuth2 clients
│   └── User.php                       # User accounts
└── Services/
    └── JwtService.php                 # JWT token management

routes/
├── api.php                            # Admin REST API
└── web.php                            # OIDC endpoints

tests/
└── Feature/
    ├── OidcEndpointsTest.php         # OIDC tests
    ├── AdminRealmApiTest.php         # Realm API tests
    ├── AdminUserApiTest.php          # User API tests
    └── AdminClientApiTest.php        # Client API tests
```

## Keycloak Compatibility

CrownID implements Keycloak's external contracts:

1. **OIDC Endpoints**: Same URL structure (`/realms/{realm}/protocol/openid-connect/*`)
2. **Admin REST API**: Keycloak-compatible paths (`/api/admin/realms/*`)
3. **Discovery Format**: Compatible JSON response structure
4. **JWT Claims**: Standard OIDC claims (sub, iss, aud, exp, etc.)
5. **Token Types**: RS256-signed JWTs for access and ID tokens
6. **JSON Representations**: Keycloak-style resource formatting

### Compatibility Target

- Keycloak version: Latest stable (validated via future golden tests)
- OpenID Connect Core 1.0
- OAuth 2.0 RFC 6749

## Roadmap

See [Plan.md](Plan.md) for the complete development plan.

### Q1 2026 - Phase 0 (✅ Complete)
- [x] OIDC Core endpoints
- [x] Basic SSO session
- [x] JWT token generation (RS256)
- [x] Multi-realm support

### Q2 2026 - Phase 2 (✅ Complete)
- [x] Admin REST API
- [x] Realm, User, and Client management
- [x] Keycloak-compatible JSON representations
- [x] Pagination and search
- [x] Comprehensive test suite

### Q2-Q3 2026 - Phase 1 (Next)
- [ ] Roles and Groups
- [ ] Keycloak-style claims (`realm_access`, `resource_access`)
- [ ] Group hierarchy
- [ ] Role mappings

### Q3 2026 - Phase 3
- [ ] Realm import/export
- [ ] Bulk operations
- [ ] Client scopes

### Q4 2026 - Phase 4
- [ ] Required actions (email verification, password reset)
- [ ] TOTP MFA
- [ ] Rate limiting / brute-force protection
- [ ] Audit event log

## Documentation

- [OIDC Testing Guide](OIDC_TESTING.md) - Manual endpoint testing
- [Plan.md](Plan.md) - Complete project specification
- [Contributing Guidelines](CONTRIBUTING.md) - Coming soon

## Security

- Uses RS256 asymmetric signing for JWTs
- Client secrets stored in plain text (OAuth 2.0 standard)
- Authorization codes expire in 90 seconds (RFC 6749 recommendation)
- Single-use authorization codes with revocation tracking
- Constant-time secret comparison

**Important**: For production use:
- Use PostgreSQL instead of SQLite
- Enable Redis for sessions and caching
- Configure proper key rotation
- Set up HTTPS with valid certificates
- Review and update security configurations

## License

MIT License - see [LICENSE](LICENSE) file

## Credits

Built with:
- [Laravel 12](https://laravel.com)
- [Laravel Passport](https://laravel.com/docs/passport) - OAuth2 server
- [Laravel Fortify](https://laravel.com/docs/fortify) - Authentication
- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission) - RBAC
- [lcobucci/jwt](https://github.com/lcobucci/jwt) - JWT handling

## Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Support

- Issues: [GitHub Issues](https://github.com/md-riaz/CrownID/issues)
- Documentation: [Wiki](https://github.com/md-riaz/CrownID/wiki)
