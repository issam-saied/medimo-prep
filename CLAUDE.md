# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

**With Docker (recommended):**
```bash
docker compose up -d        # start all services (app, mysql, nginx, queue, reverb, scheduler)
npm run dev                 # frontend hot reload (local, not in Docker)
# visit http://localhost:8000
```

**Docker first-time setup:**
```bash
cp .env.docker .env
docker compose up -d --build
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
npm install && npm run build
```

**Without Docker (local):**
```bash
composer dev
# Starts concurrently: artisan serve, queue:listen, pail (log viewer), vite dev server, reverb, schedule:work
```

**First-time setup (local):**
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

`api.js` uses `baseURL: ''` (relative URLs) so it works with both Docker (port 8000) and local dev.

### Laravel Backend Layers

| Layer | Location | Responsibility |
|---|---|---|
| Controllers | `app/Http/Controllers/` | Thin: validate → service → resource |
| FormRequests | `app/Http/Requests/` | Three per resource: `List*`, `Store*`, `Update*`. Authorization via `$user->hasJobTitle(...)` on writes |
| Services | `app/Services/` | Business rules (e.g., blocking duplicate active prescriptions) + fire domain events |
| Resources | `app/Http/Resources/` | Explicitly shaped JSON output, always including eager-loaded relations |
| Domain | `app/Domain/` | Pure PHP classes (no Laravel deps) for isolated business rules; unit-tested directly |
| Events/Listeners | `app/Events/`, `app/Listeners/` | Fired on create/update for Prescriptions, Administrations, and Patients (Created/Updated/Deleted); listeners write to `activity_logs`. Registered in `AppServiceProvider::$listen`. Login/logout are logged directly in `AuthController` (no event). |

### Vue Frontend

- **Composition API** (`<script setup>`) throughout. No Pinia/Vuex — state is local or shared via composables.
- **All components are page-level** (`resources/js/pages/`). No shared UI component library yet.
- **`useDataTable` composable** (`resources/js/composables/useDataTable.js`) is the key shared abstraction for list pages — handles pagination, sort, filter, and 500ms debounced search. All list pages use it.
- **Pages call `api.get/post/put/delete` directly** from `services/api.js` with no global store.
- **Tailwind v4** — no `tailwind.config.js`; configured via the Vite plugin.
- **`AppLayout.vue`** — shared sidebar + header shell. Sidebar nav items are filtered by `user.role` (Users and Activity Logs are admin-only). Header username links to `/profile`.
- **`NotificationBell.vue`** — fetches unread notifications on mount, listens via Laravel Echo WebSocket on private channel `App.Models.User.{id}`, closes on outside click.
- **`DashboardPage.vue`** — listens to public channel `dashboard` via Echo; re-fetches data when `administration.changed` event is received (real-time updates).
- **`ActivityLogsPage.vue`** — displays color-coded action badges (`actionClass` helper: red=deleted, green=created, blue=login/logout) and renders before/after field diffs from `log.changes` (red=from, green=to).

### Domain Model

`patients` → `prescriptions` (FK: `patient_id`, `medication_id`, `prescriber_id`, `created_by_user_id`) → `administrations` (FK: `prescription_id`, `user_id`)

`prescriptions.status`: `active | completed | stopped`  
`administrations.status`: `given | missed | refused`

`created_by_user_id` on prescriptions is always set server-side (`auth()->id()`), never from the client.  
`user_id` on administrations is always set server-side (`auth()->id()`), never from the client.  
`prescriber_id` must reference a user with `role = doctor` (enforced in `StorePrescriptionRequest`).  
`prescriptions.start_date` and `end_date` are `datetime` columns (not `date`) — allows time-precise range checks.

All 5 models (`Patient`, `Medication`, `Prescription`, `Administration`, `User`) use `SoftDeletes`. Delete buttons are admin-only, enforced via Policies.

### Testing Conventions

- Feature tests use `RefreshDatabase` + SQLite in-memory (fast, no real DB needed).
- Auth: `$this->actingAs($user)`.
- Factory states: `User::factory()->doctor()->create()`.
- Feature tests organized by domain: `tests/Feature/Prescriptions/`, `tests/Feature/Administrations/`, `tests/Feature/Patients/`, `tests/Feature/Auth/`, `tests/Feature/Medications/`, etc.
- Unit tests in `tests/Unit/` extend `PHPUnit\Framework\TestCase` directly (no Laravel bootstrap) — used for pure domain classes.
- Naming: `test_it_*` snake_case describing behavior.
- `start_date`/`end_date` in test fixtures must use `->toDateTimeString()`, not `->toDateString()`.

## Known Gotchas

- `*PageOld.vue` files (Administrations, Prescriptions) exist in `resources/js/pages/` but are not registered in the router — they are legacy iterations.
- Pagination is hardcoded to 5 per page inside `PrescriptionService::getFilteredPrescriptions()`.
- Queue is `database` in `.env` but `sync` during tests (set in `phpunit.xml`).
- Route order matters: `/patients/create` must be defined before `/patients/:id` in the router to avoid `create` being matched as an id. Same applies to API routes — `/patients/export` and `/prescriptions/export` must be registered **before** their `apiResource` to avoid `export` being matched as a record ID (returns 404 otherwise).
- `AdministrationValidator` (`app/Domain/Administration/`) handles date range checks for `administered_at` — used by `AdministrationService`, unit-tested directly.
- Scheduler: `app:expire-prescriptions` runs daily at midnight (`->daily()` in `routes/console.php`). In Docker it runs via the `scheduler` service (`php artisan schedule:work`); locally via `composer dev`. Without either, the command never fires automatically.
- Docker networking: backend services connect to Reverb via service name `reverb:8080`; browser connects via `localhost:8080`. `REVERB_HOST` and `VITE_REVERB_HOST` must be set separately in `.env`.
- `AdministrationCreated` and `AdministrationUpdated` events implement `ShouldBroadcast` and broadcast on the public `dashboard` channel as `administration.changed`.
- `activity_logs.changes` is a nullable JSON column (added in `2026_04_26` migration) storing field-level diffs as `{ field: { from: oldValue, to: newValue } }`. All entity updates (prescriptions, administrations, patients) must use this exact structure — a previous `{ before: {...}, after: {...} }` format on administrations caused empty display in Activity Logs (fixed in PR #7). Login/logout entries have no changes.
- Caching: `patient_options` and `medication_options` are cached for 1 hour and invalidated on create/update/delete. `dashboard_global_stats` is cached for 5 minutes.
- Export: `GET /api/patients/export` and `GET /api/prescriptions/export` support `?format=csv|pdf` and accept the same filter params as their `index` endpoints. PDF uses `barryvdh/laravel-dompdf` with Blade templates in `resources/views/exports/`. Frontend triggers downloads via axios blob (`responseType: 'blob'`) — required because auth is session-cookie based.
