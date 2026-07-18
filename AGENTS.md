# AGENTS.md

## Project

Get ERP Store — Laravel 13 multi-tenant SaaS for e-commerce management. Bengali/English UI, MySQL production, SQLite in-memory tests. `stancl/tenancy` v3.10 (database-per-tenant, subdomain-based).

**Local Dev URL**: `http://smm.test/`

## Quick Commands

```bash
./start.sh              # Docker + DNS fix + Apache + CLIP server + Ngrok tunnel
composer setup          # install, key:generate, migrate, npm install --ignore-scripts, build
composer dev            # artisan serve + queue:listen(--tries=1 --timeout=0) + pail + npm run dev (4 procs via concurrently)
composer test           # config:clear + artisan test
php artisan test --filter=TestName

# Multi-Tenancy
php artisan tenants:migrate        # Migrate all tenant databases
php artisan tenants:seed           # Seed all tenant databases

# Storefront (from resources/storefront/)
cd resources/storefront
npm install
npx vite build                      # Build React SPA -> public/storefront/
npx vite preview                    # Preview built storefront
```

## Architecture

### Multi-Tenancy

- **Landlord DB** (`socialboost`): `tenants`, `domains`, `admins`, `admin_user_permissions`, `ai_system_prompts`, `business_categories`, `cache`, `jobs`, `sessions`
- **Tenant DB** (`{subdomain}_socialboost`): `users`, `facebook_settings`, `ai_settings`, `conversations`, `messages`, `categories`, `attribute_templates`, `brands`, `products`, `product_attribute_values`, `product_variants`, `product_images`, `warehouses`, `stock_movements`, `inventory_alerts`, `stock_transfers`, `variant_images`, `business_settings`, `storefront_settings`, `storefront_banners`
- **Users table does NOT exist in landlord DB** — never `User::count()` from central routes
- **Tenant custom attributes** in `data` JSON column — query: `where('data->status', 'active')`, NOT `where('status', 'active')`
- **Central domains**: `127.0.0.1`, `localhost`, `smm.test`, `socialboost.com`, `www.socialboost.com`
- **Tenant migrations**: `database/migrations/tenant/` — run via `php artisan tenants:migrate`
- **Tenant routes** loaded by `TenancyServiceProvider::mapRoutes()` in `app/booted` callback, NOT via `withRouting()`

### Route Priority & Domain Blocking

- **Central routes** (`routes/web.php`): Wrapped in `PreventAccessFromNonCentralDomains` — blocks non-existent subdomains. Does NOT block valid tenant domains.
- **Webhook routes** (`/webhook/facebook`, `/webhook/zernio`): Outside middleware group — accessible from any domain
- **`facebook/zernio/test-webhook`** route has unusual middleware: `['web', InitializeTenancyByDomain::class, 'auth']` — initializes tenancy AND requires auth on a central route
- **Tenant routes** (`routes/tenant.php`): Use `InitializeTenancyByDomain` + `PreventAccessFromCentralDomains`
- **API routes** (`routes/api.php`): Registered via `withRouting(api: ...)` in `bootstrap/app.php`. Include tenancy middleware.
- **Gotcha**: Central routes registered FIRST via `withRouting`, tenant routes later via `TenancyServiceProvider::boot()`. For identical paths, central version wins.

### Tenant Route Order (CRITICAL)

Routes in `tenant.php` MUST be registered in this exact order:

1. Auth routes (login, logout, auto-login, language switch) — NO auth middleware
2. Dashboard routes (auth required, `/dashboard` prefix)
3. Storefront settings routes (auth required, `/storefront-settings` prefix)
4. **Storefront catch-all** (NO auth, LAST) — `GET /` and `GET /{path}`

If catch-all is registered first, it swallows `/dashboard`, `/login`, `/storefront-settings` etc.

### Tenant URL Structure

- **`/{subdomain}.smm.test/`** → React E-Commerce Storefront (public)
- **`/{subdomain}.smm.test/dashboard`** → Admin Dashboard (auth required)
- **`/{subdomain}.smm.test/storefront-settings`** → Theme customizer (auth)
- **`/{subdomain}.smm.test/login`** → Login page (public)

### Auth System

- **Dual auth**: `web` guard (User, tenant DB) + `admin` guard (Admin, landlord DB)
- **Admin permissions**: `super_admin` bypasses all. Defined in `config/menu.php`, stored in `admin_user_permissions`
- **Admin routes loaded via** `then:` callback in `bootstrap/app.php`, NOT through `withRouting()`
- **Middleware aliases** in `bootstrap/app.php`: `admin` → `AdminMiddleware`, `locale` → `SetLocale`, `central` → `PreventAccessFromNonCentralDomains`
- **User model** uses Laravel 11+ `#[Fillable]`/`#[Hidden]` PHP attributes. **Admin model** uses traditional `$fillable`/`$hidden` arrays
- **`Admin::getAllPermissions()`** references `\App\Services\Menu::class` which **does not exist** — will error for `super_admin` role (dead variable `$menu`, code continues but throws BindingResolutionException)

### Onboarding

- **Route**: `GET /register` and `GET /login` both redirect to `/onboarding` (no separate register page)
- **POST /register** removed — all registration through `OnboardingController@store`
- **Flow**: Single-step Alpine.js form → validate all → `Tenant::create()` + `Domain::create()` → `$tenant->run()` creates User + BusinessSetting → cross-domain auto-login via one-time token in `remember_token` → redirect to `{subdomain}.smm.test/dashboard`
- **Auto-login token**: `Str::random(64)` stored as Hash in `remember_token`, consumed once at `/auto-login?email=...&token=...`

### Localization

- **Languages**: Bengali (`bn`, default) / English (`en`)
- **Storage**: User's `locale` column in tenant DB `users` table
- **Middleware**: `SetLocale` reads `$request->user()->locale`, sets `app()->setLocale()`
- **Switcher**: `POST /language/switch` — updates locale, redirects back
- **Translation files**: `lang/bn/` and `lang/en/`
- **Blade usage**: `@lang('file.key')` or `__('file.key')`. Dynamic: `__('file.key', ['var' => $value])`

### AI Integration

- **3-tier fallback chain**: **Groq → Cerebras → Gemini**
- **Groq model**: `llama-3.3-70b-versatile` | **Cerebras**: `gpt-oss-120b` | **Gemini**: `gemini-3.1-flash-lite`
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

- **7 services**: app (8000), mysql (3307), node (5173), redis (6379), phpmyadmin (8080), worker, clip-server (8089)
- **Worker container**: Supervisor → `php artisan horizon` (`docker/supervisord.conf`). Uses pre-built image `socialmediamanager-app:latest` (not built from Dockerfile like `app` service)
- **Entrypoint** (`docker-entrypoint.sh`): waits for MySQL → composer install (only if `vendor/` missing) → npm install (only if `node_modules/` missing) → key:generate → migrate → serve
- `.env` uses `DB_HOST=mysql` (Docker service name), `.env.example` defaults to PostgreSQL

### Frontend

- Tailwind CSS v4 via `@tailwindcss/vite` plugin (no `tailwind.config.js` — config in CSS)
- **Public layouts**: Tailwind CDN + Alpine.js (not Vite)
- **Tenant/Admin**: Tailwind CDN (dashboard sidebar layout)
- **React Storefront**: `resources/storefront/` — React SPA with Vite + Tailwind CSS v3 (has own `tailwind.config.js`)
- Bengali font: Hind Siliguri (Google Fonts)
- `app.js` is empty — no JS bundled via Vite yet

### React Storefront

- **Location**: `resources/storefront/` — React SPA with Vite + Tailwind CSS v3
- **Theme System**: 2 pre-built themes (modern, classic) hardcoded in `ThemeController::THEMES` array. No themes table.
- **Database**: `storefront_settings` (one row per tenant, NO user_id) stores theme_slug + overrides + layout/contact/social/footer settings. `storefront_banners` stores hero banners with sort_order.
- **Theme Resolution**: Default CSS vars → Preset theme → Tenant overrides → Custom CSS
- **Hot-Swap**: CSS variables on `<html>` — no page reload needed for theme changes
- **FOWT Prevention**: Inject CSS vars inline in Blade `<head>` before React loads
- **API Routes** (`routes/api.php`): `/api/storefront/{config,home,products,products/{slug},categories,brands,featured}`, `/api/themes`
- **Storefront catch-all** (`routes/tenant.php`): LAST route — `GET /` and `GET /{path}` serve Blade view with React SPA. Registered AFTER dashboard/auth/storefront-settings routes.
- **Theme Customizer**: `/storefront-settings` — 3 tabs: Theme Selection, General Settings, Banner Management
- **Theme colors**: Include `header_text` for contrast — dark headers get white text, white headers get dark text.
- **Build**: `cd resources/storefront && npx vite build` → outputs to `public/storefront/` (NOT `public/build/`)
- **Asset loading**: `@vite` directive does NOT work for storefront (different output dir). Blade template uses glob to find `public/storefront/assets/index-*.js` and `index-*.css`.
- **Data flow**: Blade injects `window.__STOREFRONT_DATA__` with snake_case keys (`store_name`, `store_logo`, `theme`). React reads via `config?.store_name`, `config?.store_logo`. API also returns snake_case.
- **`App.jsx` fallback** uses camelCase (`storeName`) — inconsistent with snake_case convention. Only affects error fallback path.

### Storefront Uploads

- **Upload folders**: `storefront/logos/`, `storefront/favicons/`, `storefront/banners/` — all under `Storage::disk('public')`
- **NOT tenant-isolated**: The `public` disk is NOT in tenancy `disks` array (`config/tenancy.php:108`). All tenants share the same `public/storefront/` directory. Laravel's `store()` generates unique filenames to avoid collisions.
- **Product image API**: `StorefrontApiController` returns raw `$img->path` (NOT full Storage URL) for product/variant images. Logo/favicon return full URLs via `Storage::disk('public')->url()`.
- **`footer_logo`** field exists in DB, model `$fillable`, and API response — but has NO upload UI/route in `StorefrontSettingsController`.

## Key Paths

- **Public views**: `resources/views/` (welcome, features, pricing, about, contact, auth)
- **Onboarding views**: `resources/views/onboarding/` — single-step Alpine.js form
- **Tenant views**: `resources/views/tenant/` — index, integration, facebook-settings, ai-setup, conversations/, products/, categories/, brands/, warehouses/, inventory/, attribute-templates/, image-match/, storefront-settings/
- **Admin views**: `resources/views/admin/` (auth, dashboard, users CRUD, tenants CRUD, ai-system-prompt, business-categories CRUD)
- **React Storefront**: `resources/storefront/` — src/components/{layout,home,ui}/, src/pages/, src/contexts/, src/hooks/, src/api/, src/utils/
- **Layouts**: `resources/views/layouts/app.blade.php` (public), `resources/views/layouts/tenant.blade.php` (tenant), `resources/views/admin/layouts/app.blade.php` (admin), `resources/views/storefront.blade.php` (React SPA entry)
- **Config**: `config/menu.php` (admin sidebar), `config/tenancy.php` (central domains, DB suffix), `config/services.php` (Facebook + Groq + Cerebras + Gemini + CLIP + Zernio)
- **Controllers**: `DashboardController`, `ProductController`, `AttributeTemplateController`, `ThemeController` (hardcoded themes API), `StorefrontController` (serves Blade/React SPA), `StorefrontApiController` (public API), `StorefrontSettingsController` (admin CRUD for settings/banners)
- **Middleware**: `app/Http/Middleware/PreventAccessFromNonCentralDomains.php`, `app/Http/Middleware/SetLocale.php`

## Database

Schema source of truth: migration files in `database/migrations/` (landlord) and `database/migrations/tenant/`.

**Landlord**: `tenants` (string PK subdomain, `data` JSON), `domains`, `admins`, `admin_user_permissions`, `ai_system_prompts`, `business_categories` (with `extra_fields` JSON), `cache`, `jobs`, `sessions`

**Tenant**: `users` (`#[Fillable]` attributes, `locale` column default `bn`), `facebook_settings` (connection_type enum: `facebook_app`/`zernio`), `ai_settings` (type: `message`/`cerebras`/`image`), `conversations`, `messages`, `categories` (self-FK parent_id), `attribute_templates` (is_variant_option, is_active, placeholder/default), `brands`, `products` (weight_kg, is_featured), `product_attribute_values`, `product_variants` (JSON attributes), `product_images`, `variant_images` (JSON embedding), `warehouses`, `stock_movements`, `stock_transfers`, `inventory_alerts`, `attribute_options`, `business_settings` (delivery areas JSON, payment methods JSON, FAQ JSON, extra_fields_data JSON, logo_path), `storefront_settings` (theme_slug, theme_overrides JSON, store_logo, store_favicon, footer_logo, layout_style, products_per_row, show_header_slider, show_brands_section, show_newsletter, contact/social/footer fields, custom_css), `storefront_banners` (title, subtitle, image, link, btn_text, sort_order, is_active, FK to storefront_settings)

**Seeders**: `AdminSeeder` (`admin@socialboost.com` / `Admin@123456`, super_admin), `BusinessCategorySeeder` (8 categories with extra_fields JSON), `AttributeTemplateSeeder` (8 global variant options + category-specific extra field templates)

## Gotchas

- **`app.js` is empty** — no JS bundled via Vite yet
- **`.env.example` defaults to PostgreSQL** — actual `.env` uses MySQL
- **`APP_LOCALE=en`** — Bengali hardcoded in Blade templates, not via locale config
- **`.npmrc`**: `ignore-scripts=true` — postinstall scripts skipped
- **`storage/framework/views/`** excluded from Vite watch but NOT gitignored
- **No `pint.json`** — Laravel Pint uses defaults
- **No CI workflows** configured
- **`tailwind.config.js` exists ONLY in `resources/storefront/`** — main app uses Tailwind v4 CSS-based config
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
- **Storefront `storefront_settings`**: One row per tenant, NO user_id column. Do `StorefrontSettings::first()`, NOT `where('user_id', ...)`.
- **Storefront asset loading**: `@vite` directive does NOT work — build outputs to `public/storefront/` not `public/build/`. Blade template uses glob fallback.
- **Storefront route order**: Catch-all `/{path}` in `tenant.php` MUST be last route — it swallows all unmatched paths. Dashboard/auth/storefront-settings routes must come before it.
- **Storefront theme `header_text`**: Required for contrast. Dark `header_bg` → `header_text: #FFFFFF`, white `header_bg` → `header_text: #111827`.
- **Storefront uploads NOT tenant-isolated**: All tenants share `public/storefront/` directory. Unique filenames prevent collisions but there's no tenant-level file isolation.
- **`TenantCouldNotBeIdentifiedOnDomainException`** caught globally in `bootstrap/app.php` → returns 404.
- **`docker-entrypoint.sh` conditional installs**: Composer and npm installs only run if `vendor/` or `node_modules/` directories are missing.

# Agent Instructions

Always reply in Banglish (Bengali written in English letters).
No matter what language the user writes in, always respond in Banglish.
Example: "Ei function ta fix korte hobe, karon ekhane error ache."

Always follow best practices for coding architecture (SOLID, DRY, KISS, YAGNI).
Follow Laravel conventions and the conventions of the frameworks/libraries used.
