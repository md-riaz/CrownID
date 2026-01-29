CrownID — Keycloak-Compatible IAM Server in Laravel (Spec v1.0)

0) Compatibility Promise

CrownID’s definition of “Keycloak-compatible” is external-contract compatibility, not internal schema compatibility:

1. OIDC Provider compatibility (endpoint structure + discovery + JWT/JWKS + claims conventions) 


2. Admin REST API compatibility (path patterns + JSON representations for supported endpoints) 


3. Realm import/export compatibility (accept Keycloak realm JSON and produce a Keycloak-like realm export) 



Any feature is “done” only when:

a compatibility test compares CrownID output with a real Keycloak instance output (golden tests)

diffs are either zero or explicitly documented as “known divergence” with migration notes



---

1) Source-of-Truth Documents (Agents Must Not Deviate)

AI agents MUST refer to these as ground truth for contracts:

Keycloak OIDC endpoints and discovery patterns 

Keycloak Admin REST API (paths & representations) 

Keycloak import/export behavior and import-realm directory scanning (JSON files) 


Implementation rule: If a behavior is not in the above contracts, CrownID must implement it only when:

validated against a running Keycloak version via golden tests, or

covered by a later “RFC: Behavior Parity” document in this repo



---

2) Supported Keycloak Version Baseline

Baseline compatibility target: Keycloak latest stable at project start.

CrownID repo MUST pin a specific Keycloak Docker image tag in CI for golden tests.


> Reason: Keycloak changes behavior (e.g., endpoints historically changed around “/auth” path removals). CrownID must be version-explicit.




---

3) Naming / URL Structure

3.1 Realm URL base (MUST)

All OIDC endpoints are rooted under:

/realms/{realm-name}/...

The well-known discovery endpoint MUST be:

/realms/{realm-name}/.well-known/openid-configuration 

CrownID MUST also expose:

/realms/{realm-name}/protocol/openid-connect/*

to match Keycloak conventions described in OIDC guides. 


---

4) Core Modules and “No-Hallucination” Boundaries

Each module has: inputs, outputs, invariants, tests, and “what to copy from Keycloak”.

4.1 Modules

1. Realm Module



Realm CRUD

Realm settings used by OIDC: issuer, token TTLs, session policies


2. Identity Module



Users

Credentials (password, later TOTP)

Attributes (key/value)

Required actions (later phases)


3. Client Module



OIDC client registration (admin-side)

Redirect URIs

Confidential/public clients

Client scopes + protocol mappers (phase 2)


4. OIDC Protocol Module



Discovery document

Authorization endpoint

Token endpoint

Userinfo endpoint

Logout endpoint

JWKS endpoint

JWT signing & key rotation


5. Session Module



Browser SSO session cookie behavior

Logout propagation (CrownID session + app session via redirects)


6. Admin API Module



Keycloak-like REST endpoints under /admin/realms/... 


7. Import/Export Module



Import Keycloak realm JSON (partial in early phases)

Export realm JSON


8. Audit & Events Module



Admin actions log

Auth events log



---

5) OIDC Provider Contract (Exact Endpoints)

5.1 Discovery

GET /realms/{realm}/.well-known/openid-configuration 

Required fields (MUST)

CrownID MUST include at minimum:

issuer

authorization_endpoint

token_endpoint

userinfo_endpoint

jwks_uri

end_session_endpoint (if logout supported)

response_types_supported

subject_types_supported

id_token_signing_alg_values_supported


Golden test rule: Compare CrownID discovery JSON keys/values with Keycloak for same realm/client (allow only documented divergence).


---

5.2 Authorization Endpoint

GET/POST /realms/{realm}/protocol/openid-connect/auth 

Supported flow (Phase 0 MUST)

Authorization Code Flow:

response_type=code

client_id

redirect_uri

scope must include openid

state required (reject if missing)

nonce required for OIDC ID token (reject if missing unless Keycloak allows missing; decide by golden test)



Login behavior (MUST)

If user has SSO session at CrownID: no login UI, proceed to consent/redirect like Keycloak.

If no session: show login UI (Tyro-based or custom).


No-guessing directive: Consent behavior varies by Keycloak client settings. CrownID should implement “no consent” by default unless explicitly enabled, matching Keycloak defaults for typical confidential clients (verify via golden tests).


---

5.3 Token Endpoint

POST /realms/{realm}/protocol/openid-connect/token 

Required grant (Phase 0 MUST)

grant_type=authorization_code

Must validate:

code

redirect_uri match

client authentication (Basic or client_secret_post)



Returned tokens (MUST)

access_token (JWT)

id_token (JWT)

refresh_token (JWT or opaque; but if Keycloak returns opaque vs JWT differs—match Keycloak by baseline version via golden test)

token_type=bearer

expires_in



---

5.4 Userinfo Endpoint

GET/POST /realms/{realm}/protocol/openid-connect/userinfo 

Requires Authorization: Bearer <access_token>

Must validate token signature, exp, issuer, audience rules (match Keycloak defaults via test harness)



---

5.5 JWKS Endpoint

GET /realms/{realm}/protocol/openid-connect/certs (Keycloak commonly uses this) and/or the JWKS URI referenced from discovery; CrownID must implement exactly what discovery points to.
Golden test: discovery’s jwks_uri must be valid and return a JWKS JSON with public keys.


---

5.6 Logout Endpoint

GET/POST /realms/{realm}/protocol/openid-connect/logout (or end_session_endpoint from discovery). 

Phase 0 minimal:

Accept post_logout_redirect_uri (validate allowed)

Clear CrownID SSO session cookie

Invalidate refresh tokens (if stored)



---

6) Token Claims Contract (Keycloak-Conventional Claims)

CrownID MUST support, at minimum, the Keycloak-style role claims:

realm_access: { roles: [...] }

resource_access: { <client-id>: { roles: [...] } }


These are widely relied upon by clients. (They show up as the canonical place for roles in modern Keycloak usage.) 

Phase 0:

Issue minimal claims needed for login:

sub, iss, aud, exp, iat

preferred_username, email (if present) Phase 1:


Add role mapping behavior to populate realm_access and resource_access.


Golden tests: Use a real Keycloak realm with:

realm roles

client roles

group roles …and compare claim structure.



---

7) Admin REST API Compatibility (Phased)

Base path: /admin/realms/{realm}/... 

7.1 Phase 2: Minimum Admin API set (MUST)

Agents must implement exactly these endpoints first (paths must match Keycloak’s):

Realms

GET /admin/realms

GET /admin/realms/{realm}

POST /admin/realms

PUT /admin/realms/{realm}

DELETE /admin/realms/{realm}


Users

GET /admin/realms/{realm}/users

POST /admin/realms/{realm}/users

GET /admin/realms/{realm}/users/{id}

PUT /admin/realms/{realm}/users/{id}

DELETE /admin/realms/{realm}/users/{id}


Roles + role mappings

GET /admin/realms/{realm}/roles

POST /admin/realms/{realm}/roles

GET /admin/realms/{realm}/users/{id}/role-mappings (and client-level mappings variants later) Keycloak exposes detailed role mapping endpoints (including client role mappings). 


Clients

GET /admin/realms/{realm}/clients

POST /admin/realms/{realm}/clients

GET /admin/realms/{realm}/clients/{id}

PUT /admin/realms/{realm}/clients/{id}

DELETE /admin/realms/{realm}/clients/{id}


Representation rule: JSON fields MUST match Keycloak representations for these endpoints (derive from Keycloak OpenAPI / docs). 


---

8) Import/Export Compatibility

8.1 Keycloak-style import behavior

CrownID must support “import realm JSON files from a directory” analogous to Keycloak’s --import-realm behavior:

Scan a directory

Only .json regular files

Ignore subdirectories 


8.2 Phase 3 Import requirements

Accept a Keycloak realm export JSON and import:

realm meta

clients

roles

groups

users (if present in export form)



Keycloak’s import/export model is documented; CrownID must implement a compatible subset first and expand over time. 

Critical detail: Keycloak UI export may omit users for safety; CLI exports can include them. CrownID must support both:

realm-only exports

realm+users exports


(Enforced via fixtures from Keycloak test harness rather than blog assumptions.)


---

9) Implementation Guide (Laravel-specific, deterministic)

This section is written so agents don’t “invent” how to do it.

9.1 Laravel baseline

Laravel 11+ (project pins)

PHP 8.3+

DB: Postgres (required for production parity)

Cache: Redis (sessions + rate limits)

Queue: Redis + Horizon (jobs like email, cleanup)


9.2 Crypto & JWT rules (MUST)

Use asymmetric signing (RS256) initially

Store active keypair per realm:

kid used in JWT header


JWKS publishes public keys with matching kid

Rotation policy:

keep N old keys for verification until all tokens issued under them expire



Golden tests: Compare headers/claims to Keycloak outputs for same signing algorithm.

9.3 OAuth/OIDC server approach (MUST)

Because Passport is OAuth2-focused, CrownID must add OIDC layer explicitly:

Discovery document generator

ID token builder (JWT)

Userinfo endpoint

Session management for authorization endpoint


No-guessing directive: If you adopt a package, CrownID must include a “Compatibility Notes” doc specifying divergences vs Keycloak.

9.4 Role/Group model mapping (MUST)

CrownID must represent:

Realm roles

Client roles

Composite roles

Group hierarchy

Role mappings:

user ↔ realm roles

user ↔ client roles

group ↔ roles

user inherits group roles



Output mapping rules are validated via token claim golden tests (realm_access/resource_access). 


---

10) “AI Agents Cannot Hallucinate” Execution System

10.1 Golden Test Harness (MANDATORY)

Repo must include a CI job that:

1. Spins up Keycloak Docker at pinned version


2. Creates realm/client/users/roles/groups via Admin API


3. Executes OIDC auth code flow against Keycloak


4. Captures:

discovery JSON

JWKS

token responses

decoded JWT claims

admin API representations

realm export JSON



5. Stores these as versioned fixtures (golden files)


6. Runs same operations against CrownID


7. Diff results



Acceptance rule: A PR fails if it changes fixtures without updating documented “expected divergence”.

Keycloak Admin API is published and can drive steps 2 and 4. 

10.2 Spec-to-Tests mapping

Every feature spec must include:

Endpoint(s)

Request/response shape

Fixture name(s)

Diff tolerances (ideally zero)

Error cases


Agents implement only what the test asserts.


---

11) 1-Year Development Plan (Phases + hard deliverables)

Quarter 1 (Months 1–3): Phase 0 — OIDC Core + SSO Session

Deliverables:

Realm, Client, User basic models

OIDC endpoints: discovery/auth/token/userinfo/logout + JWKS

SSO session cookie

Minimal Admin UI (create realm/client/user)

Golden tests for:

discovery

auth code flow

token claims baseline



Source basis: Keycloak OIDC endpoint structure and discovery location. 

Quarter 2 (Months 4–6): Phase 1 — Roles/Groups + Keycloak-Style Claims

Deliverables:

realm_access/resource_access parity 

group hierarchy + inheritance

role mappings endpoints (subset)

Golden tests that compare tokens from Keycloak vs CrownID for roles


Quarter 3 (Months 7–9): Phase 2/3 — Admin API subset + Import/Export

Deliverables:

Admin endpoints listed in section 7 with Keycloak-like representations 

Import realm JSON directory behavior 

Export realm JSON matching Keycloak shape as far as supported 

Migration report: unsupported fields/mappers listed explicitly


Quarter 4 (Months 10–12): Phase 4 — Auth flows + MFA + brute-force

Deliverables:

Required actions (verify email, update password)

TOTP MFA

Rate limiting / brute-force detection

Audit event log

Golden tests that validate behavioral parity for supported flows



---

12) Package Leverage (Pinned, declared, and test-verified)

CrownID should leverage community packages, but must avoid “behavior drift”:

Use packages for internals (UI scaffolding, queues, etc.)

Never let package defaults decide protocol behavior unless golden tests confirm it matches Keycloak


Example usage guidance:

Admin UI: Tyro Dashboard (UI only)

Permissions: Spatie Permission (internal storage), but output must match Keycloak claims

MFA: Google2FA package (internal), but required-action flows must match parity tests



---

13) Definition of Done (Per Feature)

A feature is “done” only when:

1. Spec section exists with endpoints + representations


2. Keycloak fixture(s) exist (generated by harness)


3. CrownID outputs match fixtures (or documented divergence)


4. Error cases are tested (bad redirect URI, invalid code, wrong client secret, expired token)


5. Docs updated with:

supported Keycloak version

migration notes

known divergences





---

What I can expand next (still “no-hallucination”)

The next step to make this fully deterministic is to lock down the exact JSON schemas for:

Keycloak UserRepresentation

ClientRepresentation

RoleRepresentation

GroupRepresentation

RealmRepresentation


Those are defined in Keycloak’s Admin API docs/OpenAPI. 


CrownID — Package-Locked Implementation Spec (Addendum)

> Rule of this section:
If a feature can be delegated to a mature Laravel package without violating Keycloak external contracts, it MUST be delegated.




---

1. Admin UI & Backoffice (NO PROTOCOL LOGIC)

✅ Primary Package

hasinhayder/tyro-dashboard
https://github.com/hasinhayder/tyro-dashboard

Role in CrownID

Tyro Dashboard is the Admin Console shell, equivalent to Keycloak Admin UI — but:

❌ it MUST NOT contain protocol logic

❌ it MUST NOT decide security behavior

❌ it MUST NOT shape API responses

✅ it MAY manage CRUD via Admin REST API calls

✅ it MAY provide UI-only validation


Responsibilities

Realm management UI

User management UI

Client management UI

Role & group management UI

Mapper configuration UI (phase 2)


Non-responsibilities (strict)

Token issuance

Claim calculation

Session logic

OIDC/SAML behavior


> Key rule for AI agents:
Tyro is presentation only. If logic touches JWTs, OAuth, sessions, or claims → it goes elsewhere.




---

2. OAuth2 & OIDC Core (PROTOCOL AUTHORITY)

✅ Base OAuth2 Engine

laravel/passport
https://laravel.com/docs/passport

Why Passport

Mature OAuth2 server

Maintained by Laravel core

Built on league/oauth2-server

Supports auth code flow, refresh tokens, client credentials


What Passport IS allowed to do

Authorization code generation

Token persistence

Client authentication

Refresh token lifecycle


What Passport is NOT allowed to do

❌ Generate OIDC discovery docs

❌ Generate ID Tokens automatically

❌ Decide claim shapes

❌ Decide endpoint paths


Passport is a low-level OAuth engine, not an IdP.


---

✅ OIDC Layer (MANDATORY)

Because Passport ≠ OpenID Connect, CrownID MUST layer OIDC explicitly.

Required Supporting Packages

firebase/php-jwt

JWT encoding/decoding


lcobucci/jwt (optional alternative)

Structured JWT building, headers, kid



CrownID-owned OIDC components (must be explicit)

Discovery document generator

ID Token builder

Userinfo endpoint

JWKS endpoint

Logout endpoint


> AI agent rule:
If a package auto-generates OIDC behavior that is not explicitly tested against Keycloak fixtures → it MUST NOT be used.




---

3. Authentication Flows & MFA (USER LIFECYCLE)

✅ Primary Auth Flow Engine

laravel/fortify
https://laravel.com/docs/fortify

Why Fortify

Headless auth backend

Login, reset, email verification

Extensible via actions

Does not impose UI


Allowed Responsibilities

Login attempt handling

Password validation rules

Required actions scaffolding

Email verification logic

Password reset flows


Forbidden Responsibilities

❌ OAuth redirect logic

❌ Token issuance

❌ Client-based decisions


Fortify handles human authentication, not machine trust.


---

✅ MFA / TOTP

pragmarx/google2fa-laravel
https://github.com/antonioribeiro/google2fa-laravel

Usage

Implements TOTP algorithm

Backup codes stored separately

Activated via “required action” (Keycloak-style)


Behavior lock

MFA enforcement must be:

realm-configurable

user-configurable


TOTP secret NEVER exposed in tokens



---

4. Roles, Groups, Permissions (INTERNAL STORAGE ≠ EXTERNAL SHAPE)

✅ Internal RBAC Engine

spatie/laravel-permission
https://github.com/spatie/laravel-permission

Why Spatie

Proven, flexible RBAC

Groupable roles

Cachable

Familiar to Laravel devs


Critical Constraint

Spatie is internal only.

> It MUST NOT dictate:

token claim names

role nesting structure

JSON output shape




Mapping Layer (CrownID-owned)

A Role Projection Layer MUST exist:

Internal roles/groups (Spatie)
        ↓
Keycloak-compatible model
        ↓
JWT claims:
  realm_access
  resource_access

AI agents MUST treat Spatie as storage + resolution only.


---

5. Sessions & SSO

✅ Session Backend

Laravel session driver

Redis REQUIRED in production


Behavior lock

One browser session per realm

Cookie scoped to CrownID domain

Same behavior as Keycloak:

redirect → silent auth if session exists



Packages involved

Native Laravel sessions

Native CSRF

Native cookie encryption


No external SSO package allowed — behavior must match Keycloak via golden tests.


---

6. Events, Audit Logs, Brute Force Protection

✅ Event Sourcing / Audit

spatie/laravel-activitylog
https://github.com/spatie/laravel-activitylog

Usage

Admin actions

Auth events

Security events


Optional (Phase 4+)

spatie/laravel-event-sourcing

For replayable audit trails



---

✅ Rate Limiting / Abuse

Native Laravel rate limiter

Redis-backed


Keycloak-equivalent behaviors:

Login attempt limits

Temporary lockout

Incremental backoff



---

7. Admin REST API (NO UI DEPENDENCY)

Implementation style

Plain Laravel controllers

JSON resources matching Keycloak representations


Package usage

Native Laravel routing

Native request validation

No “admin framework” allowed here


Admin API must function fully without Tyro Dashboard installed.


---

8. Import / Export System

Parsing & Serialization

Native JSON handling

Native filesystem abstraction


Why no heavy package

Keycloak import/export behavior is specific and must be matched field-by-field.

Allowed helpers

illuminate/support collections

Streaming JSON parsing for large realms



---

9. Background Jobs & Scaling

✅ Queue & Workers

Native Laravel queue

Redis driver

Horizon for monitoring


Use cases

Email sending

Token cleanup

Session cleanup

Import/export processing



---

10. Testing & Golden Harness (ANTI-HALLUCINATION CORE)

Test orchestration

PHPUnit + Pest

Docker Compose for Keycloak

Snapshot testing for JSON


Packages

Native HTTP client

JSON diff tooling


AI agents MUST:

generate code only to satisfy failing tests

never “assume” protocol behavior



---

11. Summary: Package Responsibility Matrix

Subsystem	Package	Allowed	Forbidden

Admin UI	Tyro Dashboard	UI, CRUD	Protocol logic
OAuth2	Passport	OAuth flows	OIDC semantics
OIDC	CrownID core + JWT libs	Discovery, ID tokens	Guessing
Auth flows	Fortify	Login/MFA/reset	OAuth redirects
RBAC	Spatie Permission	Storage	Claim shape
MFA	Google2FA	TOTP	Token injection
Audit	Spatie Activitylog	Logs	Policy decisions
Sessions	Laravel core	SSO cookie	Token auth



---

Why this matters (project survival insight)

By locking packages per responsibility, you achieve:

contributors know where to work

AI agents don’t hallucinate protocol behavior

upgrades don’t silently break compatibility

Laravel devs feel “at home” instantly


This turns CrownID from “ambitious clone” into a credible, sustainable OSS platform.
