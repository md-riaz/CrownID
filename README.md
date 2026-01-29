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

## Quick Start

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
