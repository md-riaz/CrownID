# Phase 2/3 Implementation Complete: Import/Export Functionality

## Overview
Successfully implemented Keycloak-compatible realm import/export functionality as specified in Plan.md Section 8.

## ✅ Deliverables

### 1. Services
- **RealmExportService** (`app/Services/RealmExportService.php`)
  - Exports realm metadata, clients, roles, groups, and users
  - Supports optional user export
  - Generates Keycloak-compatible JSON structure
  - Handles composite roles and group hierarchies
  
- **RealmImportService** (`app/Services/RealmImportService.php`)
  - Imports Keycloak realm JSON format
  - Transaction-wrapped for atomicity
  - Creates/updates realms, clients, roles, groups, users
  - Preserves role mappings and composite roles
  - Handles missing users gracefully

### 2. Controllers
- **RealmExportController** (`app/Http/Controllers/Admin/RealmExportController.php`)
  - GET `/api/admin/realms/{realm}/export?includeUsers=false`
  - Returns JSON with proper Content-Disposition header
  
- **RealmImportController** (`app/Http/Controllers/Admin/RealmImportController.php`)
  - POST `/api/admin/realms/import` - Import single realm
  - POST `/api/admin/realms/import-directory` - Import directory of realms

### 3. CLI Commands
- `crownid:export {realm} {--file=} {--users}` - Export realm to JSON file
- `crownid:import {file}` - Import realm from JSON file
- `crownid:import-directory {directory}` - Import multiple realms from directory

### 4. Test Suite
**14 comprehensive tests** in `tests/Feature/RealmExportImportTest.php`:
- ✔ Export generates valid JSON structure
- ✔ Export includes clients, roles, groups
- ✔ Export with users
- ✔ Export without users
- ✔ Import creates realm correctly
- ✔ Import creates all components
- ✔ Round-trip export/import
- ✔ Import handles composite roles
- ✔ Export includes composite roles
- ✔ CLI export command
- ✔ CLI import command
- ✔ CLI import directory command
- ✔ Export maintains group hierarchy
- ✔ Import handles missing users gracefully

## 📊 Test Results
```
OK (92 tests, 348 assertions)
```
- 77 existing tests (Phase 0 & 1)
- 14 new import/export tests
- 1 example test

## 🔑 Key Features

### Export
- Realm metadata (name, displayName, enabled, token lifespans, session settings)
- All clients with configuration (clientId, secret, redirectUris)
- Realm roles and client roles
- Groups with hierarchy (parent/child relationships)
- Composite role definitions
- Users (optional) with hashed credentials
- Role mappings (user-role, group-role)
- Group role assignments

### Import
- Accepts Keycloak realm JSON format
- Directory scanning (only .json files)
- Creates/updates realm metadata
- Imports clients, roles, groups
- Imports users (if present)
- Reconstructs composite roles
- Rebuilds role mappings
- Preserves group hierarchy
- Validates data before importing

### JSON Structure (Keycloak-Compatible)
```json
{
  "realm": "realm-name",
  "displayName": "Display Name",
  "enabled": true,
  "accessTokenLifespan": 300,
  "refreshTokenLifespan": 1800,
  "ssoSessionIdleTimeout": 1800,
  "ssoSessionMaxLifespan": 36000,
  "clients": [
    {
      "id": "client-id",
      "clientId": "client-id",
      "name": "Client Name",
      "secret": "client-secret",
      "redirectUris": ["https://example.com/callback"],
      "enabled": true
    }
  ],
  "roles": {
    "realm": [
      {
        "id": "role-id",
        "name": "role-name",
        "description": "Role description",
        "composite": false
      }
    ],
    "client": {
      "client-id": [...]
    }
  },
  "groups": [
    {
      "id": "group-id",
      "name": "Group Name",
      "path": "/GroupName",
      "realmRoles": ["role1"],
      "clientRoles": {},
      "subGroups": [...]
    }
  ],
  "users": [
    {
      "id": "user-id",
      "username": "username",
      "email": "email@example.com",
      "emailVerified": true,
      "enabled": true,
      "credentials": [
        {
          "type": "password",
          "hashedSaltedValue": "$2y$...",
          "algorithm": "bcrypt"
        }
      ],
      "realmRoles": ["role1"],
      "clientRoles": {},
      "groups": ["/GroupName"]
    }
  ]
}
```

## 🔒 Security & Validation
- ✅ All input validated
- ✅ Database transactions for atomicity
- ✅ Password hashing preserved (bcrypt)
- ✅ Client secrets exported/imported securely
- ✅ Proper error handling
- ✅ File system validation for directory imports
- ✅ No SQL injection vulnerabilities

## 🎯 API Usage Examples

### Export Realm (without users)
```bash
GET /api/admin/realms/master/export
```

### Export Realm (with users)
```bash
GET /api/admin/realms/master/export?includeUsers=true
```

### Import Realm
```bash
POST /api/admin/realms/import
Content-Type: application/json

{
  "realm": { ... }
}
```

### Import Directory
```bash
POST /api/admin/realms/import-directory
Content-Type: application/json

{
  "directory": "/path/to/realm/exports"
}
```

## 🖥️ CLI Usage Examples

### Export Realm
```bash
# Export without users (default)
php artisan crownid:export master

# Export with users
php artisan crownid:export master --users

# Export to specific file
php artisan crownid:export master --file=/path/to/export.json --users
```

### Import Realm
```bash
php artisan crownid:import /path/to/realm-export.json
```

### Import Directory
```bash
php artisan crownid:import-directory /path/to/realm/exports
```

## 📝 Implementation Notes

### Design Decisions
1. **Transaction-wrapped imports** - Ensures atomicity; if any part fails, entire import rolls back
2. **Optional user export** - Security consideration; allows realm-only exports
3. **Directory scanning** - Only processes .json files, ignores subdirectories
4. **Hashed password preservation** - Maintains bcrypt hashes during export/import
5. **Composite role handling** - Two-phase import (roles first, then composites)

### Keycloak Compatibility
- JSON structure matches Keycloak export format
- Supports realm-only and realm+users exports
- Preserves group hierarchy with path notation
- Client roles organized by clientId
- Composite roles with realm/client child roles

## ✨ Next Steps

Phase 2/3 is **COMPLETE**. Ready for:
- Phase 3: Additional Admin API endpoints
- Phase 4: Advanced features (MFA, custom attributes, etc.)
- Integration testing with real Keycloak exports
- Golden tests against running Keycloak instance

## 📦 Files Changed/Created
- `app/Services/RealmExportService.php` (new)
- `app/Services/RealmImportService.php` (new)
- `app/Http/Controllers/Admin/RealmExportController.php` (new)
- `app/Http/Controllers/Admin/RealmImportController.php` (new)
- `app/Console/Commands/ExportRealmCommand.php` (new)
- `app/Console/Commands/ImportRealmCommand.php` (new)
- `app/Console/Commands/ImportRealmDirectoryCommand.php` (new)
- `routes/api.php` (modified - added 3 routes)
- `tests/Feature/RealmExportImportTest.php` (new - 14 tests)

## 🏆 Success Metrics
- ✅ All 92 tests passing
- ✅ 348 assertions validated
- ✅ Round-trip export/import verified
- ✅ CLI commands functional
- ✅ API endpoints working
- ✅ Keycloak-compatible JSON format
- ✅ No breaking changes to existing functionality
