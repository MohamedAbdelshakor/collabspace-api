# 01 — Project Setup & API Foundation

> **Phase:** 3 · **Branch:** `feature/01-project-setup` · **Status:** Done
> **Figma screens:** Serves overall Collabspace API platform architecture

## 1. Overview

Initializes the foundational backend architecture for the Collabspace platform as a pure, high-performance REST API running on **Laravel 13** and **PHP 8.4**.

All frontend scaffolding (Vite, Blade views, CSS/JS) has been stripped away. The application is connected to **MySQL 8** (`collabspace`) with all core and dev packages installed, a standardized JSON response/error envelope established, interactive OpenAPI docs configured, and automated CI quality gates (Pint, Larastan level 8, Rector, PHPUnit) enforced.

## 2. Technical Decisions & Packages 🧹 🔒 ⚡

### Packages Installed

| Package | Version | Purpose |
|---|---|---|
| `laravel/sanctum` | `^3.3` | Token-based API authentication with abilities and expiry |
| `laravel/socialite` | `^5.33` | Google OAuth SSO token verification |
| `laravel/reverb` | `^1.14` | High-speed WebSockets server for real-time chat and presence |
| `laravel/ai` | `^1.4` | Official Laravel AI SDK for agents, tools, summaries, and reports |
| `spatie/laravel-permission` | `^8.5` | Role-based and permission-based authorization |
| `spatie/laravel-query-builder` | `^7.3` | Declarative filtering, sorting, and eager loading |
| `spatie/laravel-activitylog` | `^5.1` | Audit trails and user activity tracking |
| `spatie/laravel-medialibrary` | `^11.23` | Polymorphic file attachments management |
| `barryvdh/laravel-dompdf` | `^3.1` | PDF export engine for detailed reports |
| `propaganistas/laravel-phone` | `^6.1` | Per-country phone validation based on Google libphonenumber |
| `dedoc/scramble` | `^0.13.55` | Automated OpenAPI documentation generator (`/docs/api`) |
| `larastan/larastan` | `^3.15` (dev) | Static analysis at Level 8 |
| `rector/rector` | `^2.8` (dev) | Automated code quality and type safety refactoring |
| `driftingly/rector-laravel` | `^2.7` (dev) | Laravel-specific Rector rules |

## 3. API Foundation & Standards 🧹 🔒

### 3.1 Standard Response Envelopes

Implemented through `App\Support\ApiResponse`:

**Success Response:**
```json
{
  "data": { ... },
  "meta": { ... },
  "links": { ... },
  "message": "Operation successful."
}
```

**Error Response:**
```json
{
  "message": "Resource not found.",
  "code": "NOT_FOUND",
  "errors": { ... }
}
```

### 3.2 Global Exception Handling

Configured in `bootstrap/app.php` to intercept all exceptions on API routes:
- `ValidationException` → HTTP `422` with code `VALIDATION_FAILED` and structured errors.
- `AuthenticationException` → HTTP `401` with code `UNAUTHENTICATED`.
- `AuthorizationException` / `AccessDeniedHttpException` → HTTP `403` with code `FORBIDDEN`.
- `NotFoundHttpException` / `ModelNotFoundException` → HTTP `404` with code `NOT_FOUND`.
- `TooManyRequestsHttpException` → HTTP `429` with code `TOO_MANY_REQUESTS`.

### 3.3 Localization Middleware

`App\Http\Middleware\SetLocale`:
- Reads `Accept-Language` header (`en` or `ar`).
- Sets application locale.
- Appends `Content-Language` header to response.

### 3.4 Model Strictness & Rate Limiting

Configured in `App\Providers\AppServiceProvider`:
- `Model::shouldBeStrict(! $this->app->isProduction())` to prevent lazy loading, unfillable attribute assignment, and silently discarded attributes in dev/testing.
- Named rate limiters:
  - `api`: 60 requests/min per user or IP.
  - `login`: 5 requests/min per email + IP.
  - `messages`: 30 requests/min per user.
  - `ai`: 20 requests/min per user.
  - `reports`: 5 requests/hour per user.
  - `uploads`: 20 requests/min per user.

## 4. Endpoints Implemented

| Method | URI | Description | Auth | Rate Limit |
|---|---|---|---|---|
| GET | `/` | Redirects to `/docs/api` | None | None |
| GET | `/docs/api` | Interactive OpenAPI documentation UI | None | None |
| GET | `/docs/api.json` | OpenAPI JSON schema | None | None |
| GET | `/api/v1/health` | Service health status (database, cache, app version) | None | `api` |

## 5. Security & Quality Checklist 🔒

- [x] Environment configured for MySQL 8 (`collabspace`) with strict SQL mode.
- [x] Frontend assets completely stripped; API-only response pipeline.
- [x] Model strictness enabled outside production to catch N+1 queries.
- [x] Named rate limiters registered for all future modules.
- [x] Larastan level 8 verified with 0 errors.
- [x] Laravel Pint code style formatting verified with 0 errors.
- [x] Rector type declarations and dead-code removal verified.
- [x] Automated CI pipeline configured in `.github/workflows/ci.yml`.

## 6. Tests

All 5 initial test cases passing:
- `Tests\Feature\Api\V1\HealthTest`:
  - `test_health_check_returns_successful_payload`
  - `test_api_respects_accept_language_header`
- `Tests\Feature\Api\V1\ErrorEnvelopeTest`:
  - `test_non_existent_route_returns_standard_error_envelope`
- `Tests\Feature\ExampleTest`:
  - `test_root_redirects_to_api_documentation`
- `Tests\Unit\ExampleTest`:
  - `test_that_true_is_true`
