# AGENTS.md

## Project Overview

Laravel 13.17.0 social media manager application with MySQL (MariaDB), Vite, and Tailwind CSS 4.x.

## Quick Start

```bash
# Full setup (install deps, generate key, migrate, build assets)
composer setup

# Start all dev services (server, queue, logs, vite)
composer dev

# Start individual services
php artisan serve --host=0.0.0.0 --port=8000 &
php artisan queue:listen --tries=1 --timeout=0 &
php artisan pail --timeout=0 &
npm run dev &
```

## Required Services

| Service | Port | Start Command |
|---------|------|---------------|
| MariaDB | 3306 | `sudo systemctl start mariadb` |
| Apache (phpMyAdmin) | 80 | `sudo systemctl start apache2` |
| Laravel | 8000 | `php artisan serve --host=0.0.0.0 --port=8000 &` |
| Vite | 5173 | `npm run dev` |

## Database

- **Connection:** MySQL (MariaDB)
- **Database name:** `social_media_manager`
- **User:** `root` (no password)
- **phpMyAdmin:** http://localhost/phpmyadmin

```bash
# Create database
mysql -u root -e "CREATE DATABASE IF NOT EXISTS social_media_manager;"

# Run migrations
php artisan migrate

# Reset database
php artisan migrate:fresh --seed
```

## Testing

Tests use SQLite in-memory database (no MySQL required).

```bash
# Run all tests
composer test

# Run specific test suite
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature

# Run single test file
php artisan test --filter=ExampleTest
```

## Code Style & Linting

```bash
# Format code with Pint
./vendor/bin/pint

# Fix and format
./vendor/bin/pint --fix
```

## Key Commands

| Command | Purpose |
|---------|---------|
| `composer setup` | Full project setup |
| `composer dev` | Start all dev services |
| `composer test` | Clear config + run tests |
| `php artisan` | List all artisan commands |
| `npm run build` | Build production assets |
| `npm run dev` | Start Vite dev server |

## Environment

- `.env` - Local configuration (MySQL configured)
- `.env.example` - Template file
- PHP 8.5.4 required
- Node.js required for frontend assets

## Architecture

```
app/           → Application code (Http, Models, Providers)
config/        → Configuration files
database/      → Migrations, factories, seeders
public/        → Web root (index.php)
resources/     → Views, CSS, JS
routes/        → web.php, console.php
storage/       → Logs, cache, compiled views
tests/         → Unit and Feature tests
```

## Common Gotchas

- Laravel server must run from project root directory
- MariaDB must be running for application to work (not for tests)
- Tests automatically use SQLite in-memory - no database setup needed
- Vite watches `resources/` and ignores `storage/framework/views/`
- Run `php artisan config:clear` if config changes aren't taking effect

# Agent Instructions

Always reply in Banglish (Bengali written in English letters).
No matter what language the user writes in, always respond in Banglish.
Example: "Ei function ta fix korte hobe, karon ekhane error ache.
