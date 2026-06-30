# AGENTS.md

## Project

SocialBoost AI — Laravel 13 social media & inventory management platform. Bengali UI (all user-facing text is in Bengali), MySQL in production, SQLite in-memory for tests. **Multi-tenant architecture** using `stancl/tenancy` v3.10.0 (database-per-tenant).

## Quick Commands

```bash
composer setup        # Full project setup (install, key, migrate, npm, build)
composer dev          # Concurrent: artisan serve, queue:listen, pail, vite
composer test         # Clear config cache + artisan test
php artisan test --filter=TestName   # Run single test
npx vite build        # Build frontend assets

# Multi-Tenancy
php artisan tenants:migrate        # Migrate all tenant databases
php artisan tenants:seed           # Seed all tenant databases
php artisan tenants:run migrate    # Run migration for specific tenant
```

## Architecture

### Multi-Tenancy (Database-per-Tenant)

- **Package**: `stancl/tenancy` v3.10.0 with subdomain identification
- **Landlord DB**: `social_media_manager` — stores `tenants`, `domains`, `admins`, `admin_user_permissions`, `cache`, `jobs`
- **Tenant DB**: `{subdomain}_socialboost` (e.g., `acme_socialboost`, `beta_socialboost`) — stores `users`, `sessions`, `password_reset_tokens`
- **Registration flow**: Customer registers → `Tenant::create()` → Database auto-created → User created in tenant DB → Redirects to `{subdomain}.socialboost.com`
- **Admin panel**: Stays on landlord database, can manage all tenants via `/rootadmin/tenants`
- **Tenant identification**: Subdomain-based via `InitializeTenancyByDomain` middleware
- **Central domains** (not tenant): `127.0.0.1`, `localhost`, `socialboost.com`, `www.socialboost.com`
- **ID generator**: UUID (Stancl default) — tenant IDs can be subdomain names (e.g., `acme`)
- **Custom tenant attributes** stored in `data` JSON column: `name`, `email`, `phone`, `company`, `plan`, `status`, `trial_ends_at`

### Auth System

- **Dual auth**: `web` guard (User model) for public/dashboard, `admin` guard (Admin model) for /rootadmin/*
- **Admin routes loaded separately**: `routes/admin.php` is loaded via `then:` callback in `bootstrap/app.php` — not through the standard `withRouting()` parameter. The `admin` middleware alias is registered there too.
- **Admin permissions**: Role-based (`super_admin` bypasses all checks). Permissions defined in `config/menu.php`, stored in `admin_user_permissions` table
- **Model attribute styles differ**: `User` model uses Laravel 11+ `#[Fillable]`/`#[Hidden]` PHP attributes. `Admin` model uses traditional `$fillable`/`$hidden` arrays.

## Key Paths

- Public views: `resources/views/` (welcome, features, pricing, about, contact, auth)
- Dashboard views: `resources/views/dashboard/` (only `index.blade.php` exists — routes reference `dashboard.settings`, `dashboard.leads`, etc. but those views are missing)
- Admin views: `resources/views/admin/` (auth, dashboard, users CRUD, **tenants CRUD**)
- Tenant views: `resources/views/admin/tenants/` (index, create, edit — Tailwind styled)
- Layouts: `resources/views/layouts/app.blade.php` (public), `resources/views/admin/layouts/app.blade.php` (admin)
- Menu config: `config/menu.php` — add menu groups here for admin sidebar (includes `tenant_management` group)
- Auth config: `config/auth.php` — defines `web` (User) and `admin` (Admin) guards
- Tenancy config: `config/tenancy.php` — central domains, DB suffix (`_socialboost`), tenant model, bootstrappers

## Database

### Landlord Database (`social_media_manager`)
- `tenants` — id, data (JSON: name, email, phone, company, plan, status, trial_ends_at), timestamps
- `domains` — id, domain, tenant_id (FK)
- `admins` — id, name, email, password, role
- `admin_user_permissions` — id, admin_id (FK), menu_slug, permission
- `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`

### Tenant Database (`{subdomain}_socialboost`)
- `users` — id, name, email, phone, company, password, timestamps
- `sessions` — id, user_id, ip_address, user_agent, payload
- `password_reset_tokens` — email, token, created_at

- Production: MySQL (`mysql` driver, host: `127.0.0.1`)
- Tests: SQLite in-memory (configured in `phpunit.xml`)
- **Users table is NOT in landlord DB** — only in tenant databases

## Docker

```bash
docker compose up -d --build  # Build and start all containers
docker compose down           # Stop all containers
docker compose logs -f        # Follow logs
docker compose ps             # Check container status
docker exec laravel-app php artisan <command>  # Run artisan inside container
```

- **Custom domain**: `http://smm.test:8000/` (add `127.0.0.1 smm.test` to `/etc/hosts`)
- App on port **8000**, Vite dev server on port **5173**
- **MySQL 8.0** runs inside Docker (service name: `mysql`, host port: `3307` to avoid conflict with local MySQL on `3306`)
- `.env` uses `DB_HOST=mysql` (Docker service name), `DB_DATABASE=socialboost`, `DB_PASSWORD=secret`
- Entrypoint waits for MySQL health check, then runs `composer install --no-dev`, `npm install`, `key:generate`, `migrate`
- Landlord DB includes `sessions` table (needed for `database` session driver on public/central routes)

## Frontend

- Tailwind CSS v4 via `@tailwindcss/vite` plugin
- Vite entry: `resources/css/app.css`, `resources/js/app.js` (app.js is empty — no JS bundled via Vite yet)
- Public layouts load Tailwind via CDN (`cdn.tailwindcss.com`) + Alpine.js — **not** via Vite
- Bengali font: Hind Siliguri (Google Fonts in public layouts), Instrument Sans (Vite fonts plugin)
- Admin panel & tenant views use Tailwind via CDN

## Testing

- PHPUnit 12 with `tests/Unit/` and `tests/Feature/` suites
- Tests use SQLite in-memory, sync queue, array cache/session/mail, Pulse/Telescope/Nightwatch disabled
- `composer test` clears config cache before running (important for env overrides)

## Gotchas

- `app.js` is empty — no JS bundled via Vite yet
- No `pint.json` — Laravel Pint uses defaults
- No CI workflows configured
- `storage/framework/views/` is excluded from Vite watch but NOT gitignored
- `.npmrc` has `ignore-scripts=true` — postinstall scripts skipped
- `APP_LOCALE=en` in `.env` — Bengali is hardcoded in view templates, not set via locale config
- **Tenancy**: User model does NOT use `BelongsToTenant` trait (database-per-tenant approach makes it unnecessary)
- **Tenancy**: Tenant custom attributes (name, email, etc.) are stored in `data` JSON column, accessed via `$tenant->name`
- **Tenancy**: Tests may fail if SQLite PDO driver is missing (pre-existing issue)

# Agent Instructions

Always reply in Banglish (Bengali written in English letters).
No matter what language the user writes in, always respond in Banglish.
Example: "Ei function ta fix korte hobe, karon ekhane error ache.
