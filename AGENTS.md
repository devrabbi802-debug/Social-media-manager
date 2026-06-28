# AGENTS.md

## Project Overview

Laravel 13.x social media manager + inventory management app. MySQL (MariaDB) backend, Vite + Tailwind CSS 4.x frontend. All UI text is in Bangla.

## Quick Start

```bash
# Full setup (install deps, generate key, migrate, build assets)
composer setup

# Start all dev services (server, queue, logs, vite)
composer dev
```

## Required Services

| Service | Port | Start Command |
|---------|------|---------------|
| MariaDB | 3306 | `sudo systemctl start mariadb` |
| Laravel | 8000 | `php artisan serve --host=0.0.0.0 --port=8000 &` |
| Vite | 5173 | `npm run dev` |

## Database

- **Connection:** MySQL (MariaDB)
- **Database name:** `social_media_manager`
- **User:** `root` (no password)

```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS social_media_manager;"
php artisan migrate
php artisan migrate:fresh --seed
```

## Testing

Tests use SQLite in-memory (no MySQL needed).

```bash
composer test                    # clear config + run all tests
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature
php artisan test --filter=ExampleTest
```

## Code Style

```bash
./vendor/bin/pint        # format
./vendor/bin/pint --fix  # fix + format
```

## Key Commands

| Command | Purpose |
|---------|---------|
| `composer setup` | Full project setup |
| `composer dev` | Start all dev services |
| `composer test` | Clear config + run tests |
| `npm run build` | Production assets |
| `npm run dev` | Vite dev server |

## Architecture

```
app/Http/Controllers/   → Route controllers (currently minimal, routes use closures)
app/Models/             → Eloquent models (User)
config/                 → Laravel config files
database/migrations/    → DB schema (users, cache, jobs tables)
resources/views/        → Blade templates
resources/views/auth/   → Login, register pages
resources/views/dashboard/ → Client dashboard
resources/views/layouts/   → Main layout (app.blade.php)
routes/web.php          → All web routes (auth logic inline, not in controllers)
tests/                  → Unit + Feature tests
```

## Gotchas

- **Auth routes are inline** in `routes/web.php` (POST login/register/logout), not in controllers. If adding auth features, edit `routes/web.php`.
- **MariaDB must be running** for app to work. Tests don't need it.
- Vite watches `resources/` and ignores `storage/framework/views/`
- Run `php artisan config:clear` if config changes aren't taking effect
- `.env` has MySQL configured with `root` user, no password

## Agent Instructions

Always reply in Banglish (Bengali written in English letters).
No matter what language the user writes in, always respond in Banglish.
Example: "Ei function ta fix korte hobe, karon ekhane error ache."
