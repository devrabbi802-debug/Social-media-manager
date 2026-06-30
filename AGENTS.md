# AGENTS.md

## Project

SocialBoost AI — Laravel 13 social media & inventory management platform. Bengali UI (all user-facing text is in Bengali), PostgreSQL in production, SQLite in-memory for tests.

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
- **Admin routes loaded separately**: `routes/admin.php` is loaded via `then:` callback in `bootstrap/app.php` — not through the standard `withRouting()` parameter. The `admin` middleware alias is registered there too.
- **Admin permissions**: Role-based (`super_admin` bypasses all checks). Permissions defined in `config/menu.php`, stored in `admin_user_permissions` table
- **Model attribute styles differ**: `User` model uses Laravel 11+ `#[Fillable]`/`#[Hidden]` PHP attributes. `Admin` model uses traditional `$fillable`/`$hidden` arrays.

## Key Paths

- Public views: `resources/views/` (welcome, features, pricing, about, contact, auth)
- Dashboard views: `resources/views/dashboard/` (only `index.blade.php` exists — routes reference `dashboard.settings`, `dashboard.leads`, etc. but those views are missing)
- Admin views: `resources/views/admin/` (auth, dashboard, users CRUD)
- Layouts: `resources/views/layouts/app.blade.php` (public), `resources/views/admin/layouts/app.blade.php` (admin)
- Menu config: `config/menu.php` — add menu groups here for admin sidebar
- Auth config: `config/auth.php` — defines `web` (User) and `admin` (Admin) guards

## Database

- Production: PostgreSQL (`pgsql` driver, host: `postgres`)
- Tests: SQLite in-memory (configured in `phpunit.xml`)
- Key tables: `users`, `admins`, `admin_user_permissions`, `cache`, `jobs`

## Docker

```bash
docker compose up     # Starts app (PHP 8.4), node (Node 20), postgres (16-alpine)
```

- App on port 8000, Vite dev server on port 5173, Postgres on 5432
- Entrypoint conditionally runs `composer install --no-dev`, `npm install`, `key:generate`, `migrate`

## Frontend

- Tailwind CSS v4 via `@tailwindcss/vite` plugin
- Vite entry: `resources/css/app.css`, `resources/js/app.js` (app.js is empty — no JS bundled via Vite yet)
- Public layouts load Tailwind via CDN (`cdn.tailwindcss.com`) + Alpine.js — **not** via Vite
- Bengali font: Hind Siliguri (Google Fonts in public layouts), Instrument Sans (Vite fonts plugin)

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

# Agent Instructions

Always reply in Banglish (Bengali written in English letters).
No matter what language the user writes in, always respond in Banglish.
Example: "Ei function ta fix korte hobe, karon ekhane error ache.
