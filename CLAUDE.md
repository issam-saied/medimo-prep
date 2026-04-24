# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

**Full dev stack (recommended):**
```bash
composer dev
# Starts concurrently: artisan serve, queue:listen, pail (log viewer), vite dev server
```

**First-time setup:**
```bash
composer setup
# Runs: composer install, .env copy, key:generate, migrate, npm install, npm run build
```

**Tests:**
```bash
composer test                              # All tests (clears config cache first)
php artisan test --filter test_method_name # Single test by method name
php artisan test tests/Feature/Prescriptions/CreatePrescriptionTest.php  # Single file
php artisan test --testsuite=Feature       # By suite (Unit or Feature)
```

**Linting (Laravel Pint):**
```bash
./vendor/bin/pint          # Fix
./vendor/bin/pint --test   # Check only
```

**Database:**
```bash
php artisan migrate:fresh --seed
```

## Architecture

### Overview

Medical medication management app. Laravel 13 serves as both the API and SPA host — the web router catches all routes with a catch-all that returns the Blade shell mounting the Vue 3 SPA. All data routes are under `/api` and protected by `auth:sanctum`.

### Authentication

Session-cookie based (not Bearer tokens). Vue must call `getCsrfCookie()` via `services/api.js` before the first POST to `/login`. All Axios requests use `withCredentials: true`. The Vue Router guard calls `GET /api/me` on every protected navigation — there is no cached auth state in the frontend.

`api.js` has a hardcoded `baseURL` of `http://127.0.0.1:8000` — must match the port artisan serve uses.

### Laravel Backend Layers

| Layer | Location | Responsibility |
|---|---|---|
| Controllers | `app/Http/Controllers/` | Thin: validate → service → resource |
| FormRequests | `app/Http/Requests/` | Three per resource: `List*`, `Store*`, `Update*`. Authorization via `$user->hasJobTitle(...)` on writes |
| Services | `app/Services/` | Business rules (e.g., blocking duplicate active prescriptions) + fire domain events |
| Resources | `app/Http/Resources/` | Explicitly shaped JSON output, always including eager-loaded relations |
| Domain | `app/Domain/` | Pure PHP classes (no Laravel deps) for isolated business rules; unit-tested directly |
| Events/Listeners | `app/Events/`, `app/Listeners/` | Fired on create/update for Prescriptions and Administrations; listeners write to `activity_logs`. Registered in `AppServiceProvider::$listen` |

### Vue Frontend

- **Composition API** (`<script setup>`) throughout. No Pinia/Vuex — state is local or shared via composables.
- **All components are page-level** (`resources/js/pages/`). No shared UI component library yet.
- **`useDataTable` composable** (`resources/js/composables/useDataTable.js`) is the key shared abstraction for list pages — handles pagination, sort, filter, and 500ms debounced search. All list pages use it.
- **Pages call `api.get/post/put/delete` directly** from `services/api.js` with no global store.
- **Tailwind v4** — no `tailwind.config.js`; configured via the Vite plugin.

### Domain Model

`patients` → `prescriptions` (FK: `patient_id`, `medication_id`, `prescriber_id`, `created_by_user_id`) → `administrations` (FK: `prescription_id`, `user_id`)

`prescriptions.status`: `active | completed | stopped`  
`administrations.status`: `given | missed | refused`

`created_by_user_id` on prescriptions is always set server-side (`auth()->id()`), never from the client.

### Testing Conventions

- Feature tests use `RefreshDatabase` + SQLite in-memory (fast, no real DB needed).
- Auth: `$this->actingAs($user)`.
- Factory states: `User::factory()->doctor()->create()`.
- Feature tests organized by domain: `tests/Feature/Prescriptions/`, `tests/Feature/Administrations/`.
- Unit tests extend `PHPUnit\Framework\TestCase` directly (no Laravel bootstrap).
- Naming: `test_it_*` snake_case describing behavior.

## Known Gotchas

- `*PageOld.vue` files (Administrations, Prescriptions) exist in `resources/js/pages/` but are not registered in the router — they are legacy iterations.
- Pagination is hardcoded to 5 per page inside `PrescriptionService::getFilteredPrescriptions()`.
- Queue is `database` in `.env` but `sync` during tests (set in `phpunit.xml`).
