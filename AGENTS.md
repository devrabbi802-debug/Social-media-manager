# AGENTS.md

## Project

SocialBoost AI — Laravel 13 multi-tenant SaaS for social media management. Bengali/English UI, MySQL production, SQLite in-memory tests. `stancl/tenancy` v3.10 (database-per-tenant, subdomain-based).

**Local Dev URL**: `http://smm.test/`

## Quick Commands

```bash
./start.sh              # Docker + DNS fix + Apache + CLIP server + Ngrok tunnel
composer setup          # install, key:generate, migrate, npm install, build
composer dev            # artisan serve + queue:listen + pail + npm run dev (4 procs via concurrently)
composer test           # config:clear + artisan test
php artisan test --filter=TestName
npx vite build

# Multi-Tenancy
php artisan tenants:migrate        # Migrate all tenant databases
php artisan tenants:seed           # Seed all tenant databases
```

## Architecture

### Multi-Tenancy

- **Landlord DB** (`socialboost`): `tenants`, `domains`, `admins`, `admin_user_permissions`, `ai_system_prompts`, `business_categories`, `cache`, `jobs`, `sessions`
- **Tenant DB** (`{subdomain}_socialboost`): `users`, `facebook_settings`, `ai_settings`, `conversations`, `messages`, `categories`, `attribute_templates`, `brands`, `products`, `product_attribute_values`, `product_variants`, `product_images`, `warehouses`, `stock_movements`, `inventory_alerts`, `stock_transfers`, `variant_images`, `business_settings`
- **Users table does NOT exist in landlord DB** — never `User::count()` from central routes
- **Tenant custom attributes** in `data` JSON column — query: `where('data->status', 'active')`, NOT `where('status', 'active')`
- **Central domains**: `127.0.0.1`, `localhost`, `smm.test`, `socialboost.com`, `www.socialboost.com`
- **Tenant migrations**: `database/migrations/tenant/` — run via `php artisan tenants:migrate`
- **Tenant routes** loaded by `TenancyServiceProvider::mapRoutes()` in `app/booted` callback, NOT via `withRouting()`

### Route Priority & Domain Blocking

- **Central routes** (`routes/web.php`): Wrapped in `PreventAccessFromNonCentralDomains` — blocks non-existent subdomains (e.g. `rabbi.smm.test` → 404). **Does NOT block valid tenant domains** — a tenant visiting `smm.test/dashboard` gets the central view (no tenant context)
- **Webhook routes** (`/webhook/facebook`, `/webhook/zernio`): Outside middleware group — accessible from any domain (Facebook/Zernio calls via ngrok tunnel)
- **Tenant routes** (`routes/tenant.php`): Use `InitializeTenancyByDomain` + `PreventAccessFromCentralDomains` — blocks central domains from accessing tenant routes
- **Gotcha**: Central routes registered FIRST via `withRouting`, tenant routes later via `TenancyServiceProvider::boot()`. For identical paths (e.g. `/dashboard`), the central `web.php` version wins. Tenant routes rely on `InitializeTenancyByDomain` middleware to switch DB context.

### Auth System

- **Dual auth**: `web` guard (User, tenant DB) + `admin` guard (Admin, landlord DB)
- **Admin permissions**: `super_admin` bypasses all. Defined in `config/menu.php`, stored in `admin_user_permissions`
- **Admin routes loaded via** `then:` callback in `bootstrap/app.php`, NOT through `withRouting()`
- **Middleware aliases** in `bootstrap/app.php`: `admin` → `AdminMiddleware`, `locale` → `SetLocale`, `central` → `PreventAccessFromNonCentralDomains`
- **User model** uses Laravel 11+ `#[Fillable]`/`#[Hidden]` PHP attributes. **Admin model** uses traditional `$fillable`/`$hidden` arrays
- **`Admin::getAllPermissions()`** references `\App\Services\Menu::class` which **does not exist** — will error if called by non-super_admin

### Onboarding

- **Route**: `GET /register` and `GET /login` both redirect to `/onboarding` (no separate register page)
- **POST /register** removed — all registration through `OnboardingController@store`
- **Flow**: Single-step Alpine.js form → validate all → `Tenant::create()` + `Domain::create()` → `$tenant->run()` creates User + BusinessSetting → cross-domain auto-login via one-time token in `remember_token` → redirect to `{subdomain}.smm.test/dashboard`
- **Auto-login token**: `Str::random(64)` stored as Hash in `remember_token`, consumed once at `/auto-login?email=...&token=...`
- **Custom category**: If `custom_category_name` provided (no `category_id`), creates in `business_categories` with slug, icon 📦, sort_order 99

### Localization

- **Languages**: Bengali (`bn`, default) / English (`en`)
- **Storage**: User's `locale` column in tenant DB `users` table
- **Middleware**: `SetLocale` reads `$request->user()->locale`, sets `app()->setLocale()`
- **Switcher**: `POST /language/switch` — updates locale, redirects back
- **Translation files**: `lang/bn/` and `lang/en/` (sidebar, common, tenant, nav, dashboard, settings, integration, conversations, facebook, ai, image_match, products, categories, brands, warehouses, inventory, attributes, auth)
- **Blade usage**: `@lang('file.key')` or `__('file.key')`. Dynamic: `__('file.key', ['var' => $value])`

### AI Integration

- **3-tier fallback chain**: **Groq → Cerebras → Gemini**
- **CLIP Server** (local Python FastAPI) for image recognition — `http://localhost:8089` (local) / `http://clip-server:8089` (Docker)
- **`AiChatService`** (`app/Services/`) — Multi-provider wrapper, key rotation within each provider before falling back to next
- **`SendAiReplyJob`** — queued on `facebook` queue, 5 tries, 15s backoff, 180s timeout
- **Queue**: Redis, Horizon supervisor (1-10 processes, 256MB memory)

### Facebook / Zernio

- **Dual connection modes**: Facebook App (direct) OR Zernio (third-party API)
- **Webhook** in `routes/web.php` (central, NOT tenant) — Facebook calls via ngrok tunnel
- **CSRF exemption**: `webhook/*` and `facebook/zernio/test-webhook` in `bootstrap/app.php`
- **Verify token**: `socialboost_verify_token_2026` (hardcoded)
- **Multi-tenant webhook**: `FacebookWebhookController` iterates ALL tenants to find matching `page_id` (O(n))
- **Zernio profile names** must be unique — controller appends `time()` to avoid conflicts
- **`tempToken`** from OAuth callback required for page selection — stored in session

### Docker

```bash
docker compose up -d --build
docker compose down
docker exec laravel-app php artisan <command>
```

- **7 services**: app, mysql (3307), node (5173), redis, phpmyadmin (8080), worker, clip-server (8089)
- **Worker container**: Supervisor → `php artisan horizon` (`docker/supervisord.conf`)
- **Entrypoint** (`docker-entrypoint.sh`): waits for MySQL → composer install → npm install → key:generate → migrate → serve
- `.env` uses `DB_HOST=mysql` (Docker service name), `.env.example` defaults to PostgreSQL

### Frontend

- Tailwind CSS v4 via `@tailwindcss/vite` plugin (no `tailwind.config.js` — config in CSS)
- **Public layouts**: Tailwind CDN + Alpine.js (not Vite)
- **Tenant/Admin**: Tailwind CDN (dashboard sidebar layout)
- Bengali font: Hind Siliguri (Google Fonts), Instrument Sans (Vite fonts plugin via `bunny()`)
- `app.js` is empty — no JS bundled via Vite yet

## Key Paths

- **Public views**: `resources/views/` (welcome, features, pricing, about, contact, auth)
- **Onboarding views**: `resources/views/onboarding/` — single-step Alpine.js form
- **Tenant views**: `resources/views/tenant/` — index, integration, facebook-settings, facebook-select-page, ai-setup, conversations/, products/, categories/, brands/, warehouses/, inventory/, attribute-templates/, image-match/
- **Admin views**: `resources/views/admin/` (auth, dashboard, users CRUD, tenants CRUD, ai-system-prompt, business-categories CRUD)
- **Layouts**: `resources/views/layouts/app.blade.php` (public), `resources/views/layouts/tenant.blade.php` (tenant), `resources/views/admin/layouts/app.blade.php` (admin)
- **Config**: `config/menu.php` (admin sidebar), `config/tenancy.php` (central domains, DB suffix), `config/services.php` (Facebook + Groq + Cerebras + Gemini + CLIP + Zernio)
- **Controllers**: `DashboardController` (settings, leads, reports, whatsapp, facebook, inventory, integration), `ProductController` (CRUD + variants + extra fields), `AttributeTemplateController` (variant options + category templates)
- **Middleware**: `app/Http/Middleware/PreventAccessFromNonCentralDomains.php`, `app/Http/Middleware/SetLocale.php`

## Database

Schema source of truth: migration files in `database/migrations/` (landlord) and `database/migrations/tenant/`.

**Landlord**: `tenants` (string PK subdomain, `data` JSON), `domains`, `admins`, `admin_user_permissions`, `ai_system_prompts`, `business_categories` (with `extra_fields` JSON), `cache`, `jobs`, `sessions`

**Tenant**: `users` (`#[Fillable]` attributes, `locale` column default `bn`), `facebook_settings` (connection_type enum: `facebook_app`/`zernio`), `ai_settings` (type: `message`/`cerebras`/`image`), `conversations`, `messages`, `categories` (self-FK parent_id), `attribute_templates` (is_variant_option, is_active, placeholder/default), `brands`, `products` (weight_kg), `product_attribute_values`, `product_variants` (JSON attributes), `product_images`, `variant_images` (JSON embedding), `warehouses`, `stock_movements`, `stock_transfers`, `inventory_alerts`, `attribute_options`, `business_settings` (delivery areas JSON, payment methods JSON, FAQ JSON, extra_fields_data JSON, logo_path)

**Seeders**: `AdminSeeder` (`admin@socialboost.com` / `Admin@123456`, super_admin), `BusinessCategorySeeder` (8 categories with extra_fields JSON), `AttributeTemplateSeeder` (8 global variant options + category-specific extra field templates)

## Gotchas

- **`app.js` is empty** — no JS bundled via Vite yet
- **`.env.example` defaults to PostgreSQL** — actual `.env` uses MySQL
- **`APP_LOCALE=en`** — Bengali hardcoded in Blade templates, not via locale config
- **`.npmrc`**: `ignore-scripts=true` — postinstall scripts skipped
- **`storage/framework/views/`** excluded from Vite watch but NOT gitignored
- **No `pint.json`** — Laravel Pint uses defaults
- **No CI workflows** configured
- **No `tailwind.config.js`** — Tailwind v4 uses CSS-based config
- **Registration validation** does NOT use `unique:users,email` (users are per-tenant)
- **Facebook OAuth**: HTTP not allowed — use ngrok HTTPS URL
- **Facebook test users**: Dev mode only testers/admins trigger webhooks
- **Horizon**: runs inside Docker worker container via Supervisor, not directly
- **Redis**: queue + cache driver in `.env` (but Redis tenancy bootstrapper commented out in `config/tenancy.php`)
- **Stock operations** wrapped in `DB::transaction()` — `Product::recalculateStock()` after variant changes
- **`InventoryAlert::isLowStock()`** — variant-aware (checks variant stock if variants exist)
- **`BusinessCategory`** lives in landlord DB — always `BusinessCategory::on('mysql')->find($id)`
- **`attribute_templates.category_id`** references tenant `categories.id`, NOT `business_categories.id`
- **PHP 8.4**: Unparenthesized nested ternary `a ? b : c ? d : e` is **forbidden**
- **Alpine.js**: Reactive state must be inside `x-data` scope; `x-if` must be on `<template>` tags only
- **Route conflicts**: Central `web.php` routes registered before tenant `tenant.php` routes. For identical paths, central version wins.
- **CLIP server URL**: `http://localhost:8089` (local) vs `http://clip-server:8089` (Docker)

# Agent Instructions

Always reply in Banglish (Bengali written in English letters).
No matter what language the user writes in, always respond in Banglish.
Example: "Ei function ta fix korte hobe, karon ekhane error ache."

Always follow best practices for coding architecture (SOLID, DRY, KISS, YAGNI).
Follow Laravel conventions and the conventions of the frameworks/libraries used.
