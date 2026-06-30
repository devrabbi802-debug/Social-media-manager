# AGENTS.md

## Project

SocialBoost AI — Laravel 13 social media & inventory management platform. Bengali locale UI, PostgreSQL in production, SQLite in-memory for tests.

## Quick Commands

```bash
composer setup        # Full project setup (install, key, migrate, npm, build)
composer dev          # Concurrent: artisan serve, queue:listen, pail, vite
composer test         # Clear config cache + artisan test
php artisan test --filter=TestName   # Run single test
npx vite build        # Build frontend assets
```

## Architecture

- **Dual auth**: `web` guard (User model) for public/dashboard, `admin` guard (Admin model) for /rootadmin/*
- **Route files**: `routes/web.php` (public + dashboard), `routes/admin.php` (admin panel), `routes/console.php`
- **Admin permissions**: Role-based (`super_admin` bypasses all checks). Permissions defined in `config/menu.php`, stored in `admin_user_permissions` table
- **Models use Laravel 11+ attribute syntax**: `User` model uses `#[Fillable]` / `#[Hidden]` attributes instead of `$fillable` array. `Admin` model still uses traditional `$fillable`

## Key Paths

- Public views: `resources/views/` (welcome, features, pricing, about, contact, auth)
- Dashboard views: `resources/views/dashboard/` (only `index.blade.php` exists)
- Admin views: `resources/views/admin/` (auth, dashboard, users CRUD)
- Layouts: `resources/views/layouts/app.blade.php` (public), `resources/views/admin/layouts/app.blade.php` (admin)
- Menu config: `config/menu.php` — add menu groups here for admin sidebar

## Database

- Production: PostgreSQL (`pgsql` driver, host: `postgres`)
- Tests: SQLite in-memory (configured in `phpunit.xml`)
- Migrations: `database/migrations/`
- Key tables: `users`, `admins`, `admin_user_permissions`, `cache`, `jobs`

## Docker

```bash
docker compose up     # Starts app (PHP 8.4), node (Node 20), postgres (16-alpine)
```

- App on port 8000, Vite dev server on port 5173, Postgres on 5432
- Entrypoint auto-runs `composer install`, `npm install`, `key:generate`, `migrate`

## Frontend

- Tailwind CSS v4 via `@tailwindcss/vite` plugin
- Vite entry: `resources/css/app.css`, `resources/js/app.js`
- Public layouts load Tailwind via CDN (`cdn.tailwindcss.com`) + Alpine.js — **not** via Vite
- Bengali font: Hind Siliguri (Google Fonts in public layouts), Instrument Sans (Vite fonts plugin)

## Testing

- PHPUnit 12 with `tests/Unit/` and `tests/Feature/` suites
- Tests use SQLite in-memory, sync queue, array cache/session/mail
- `composer test` clears config cache before running (important for env overrides)

## Gotchas

- `app.js` is empty — no JS bundled via Vite yet
- No `pint.json` — Laravel Pint uses defaults
- No CI workflows configured
- `storage/framework/views/` is gitignored and excluded from Vite watch
- `.npmrc` has `ignore-scripts=true` — postinstall scripts skipped
