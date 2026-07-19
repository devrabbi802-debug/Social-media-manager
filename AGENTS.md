# AGENTS.md

## Project

Get ERP Store — Laravel 13 multi-tenant SaaS for e-commerce management. Bengali/English UI, MySQL production, SQLite in-memory tests. `stancl/tenancy` v3.10 (database-per-tenant, subdomain-based).

**Local Dev URL**: `http://smm.test/`

## Quick Commands

```bash
./start.sh              # Docker + DNS fix + Apache + CLIP server + Ngrok tunnel
./setup-domain.sh       # DNS wildcard (*.smm.test) + Apache vhost + Docker — first-time setup
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
- **Tenant DB** (`{subdomain}_socialboost`): `users`, `facebook_settings`, `ai_settings`, `conversations`, `messages`, `categories`, `attribute_templates`, `attribute_options`, `brands`, `products`, `product_attribute_values`, `product_variants`, `product_images`, `warehouses`, `stock_movements`, `inventory_alerts`, `stock_transfers`, `variant_images`, `business_settings`, `storefront_settings`, `storefront_banners`
- **Users table does NOT exist in landlord DB** — never `User::count()` from central routes
- **Tenant custom attributes** in `data` JSON column — query: `where('data->status', 'active')`, NOT `where('status', 'active')`
- **Central domains**: `127.0.0.1`, `localhost`, `smm.test`, `socialboost.com`, `www.socialboost.com`
- **Tenant migrations**: `database/migrations/tenant/` — run via `php artisan tenants:migrate`
- **Tenant routes** loaded by `TenancyServiceProvider::mapRoutes()` in `app/booted` callback, NOT via `withRouting()`

### Route Priority & Domain Blocking

- **Central routes** (`routes/web.php`): Wrapped in `PreventAccessFromNonCentralDomains` — **only allows central domains** (tenant domains get 404). This ensures central routes (welcome page, features, etc.) don't match on tenant subdomains.
- **Landing page** (`GET /`): **Domain-constrained** to each central domain via `Route::domain($domain)`. Prevents Laravel's exact-match priority from serving the welcome view on tenant subdomains.
- **Webhook routes** (`/webhook/facebook`, `/webhook/zernio`): Outside middleware group — accessible from any domain
- **`facebook/zernio/test-webhook`** route has unusual middleware: `['web', InitializeTenancyByDomain::class, 'auth']` — initializes tenancy AND requires auth on a central route
- **Tenant routes** (`routes/tenant.php`): Use `InitializeTenancyByDomain` + `PreventAccessFromCentralDomains`
- **API routes** (`routes/api.php`): Registered via `withRouting(api: ...)` in `bootstrap/app.php`. Include tenancy middleware.
- **Gotcha**: Central routes registered FIRST via `withRouting`, tenant routes later via `TenancyServiceProvider::boot()`. Exact `GET /` would win over `/{path?}` — that's why the landing page uses domain constraints.

### Admin Panel Prefix (CRITICAL)

- Tenant dashboard/admin routes are NOT at `/dashboard` — they're under `/{APP_ADMIN_PANEL_PREFIX}/`
- **`.env.example`** has `ADMIN_PANEL_PREFIX=ax7k9m`, actual **`.env`** has `ADMIN_PANEL_PREFIX=supermaster`
- The prefix is read via `$adminPrefix = config('app.admin_panel_prefix', 'ax7k9m')` in `routes/tenant.php:39`
- **Correct tenant URLs**:
  - `/{subdomain}.smm.test/supermaster/dashboard` → Admin Dashboard (auth required)
  - `/{subdomain}.smm.test/supermaster/storefront-settings` → Theme customizer (auth)
  - `/{subdomain}.smm.test/supermaster/login` → Login page (public)
  - `/{subdomain}.smm.test/` → React E-Commerce Storefront (public)
- **Central admin panel** is separate: `/{centralDomain}/rootadmin/dashboard` (uses `admin` guard, landlord DB)

### Tenant Route Order (CRITICAL)

Routes in `tenant.php` MUST be registered in this exact order:

1. Language switch (`POST /language/switch`) — NO auth, outside `$adminPrefix`
2. Auth routes inside `$adminPrefix` (auto-login, login, logout) — NO auth middleware
3. Dashboard/inventory/AI/facebook/storefront-settings routes inside `$adminPrefix` with `auth` middleware
4. Register/onboarding redirects on tenant (block customers from signup)
5. **Storefront catch-all** (NO auth, LAST) — `GET /{path?}` with optional param

If catch-all is registered first, it swallows `/dashboard`, `/login`, `/storefront-settings` etc.

### Auth System

- **Dual auth**: `web` guard (User, tenant DB) + `admin` guard (Admin, landlord DB)
- **Admin permissions**: `super_admin` bypasses all. Defined in `config/menu.php`, stored in `admin_user_permissions`
- **Admin routes loaded via** `then:` callback in `bootstrap/app.php`, NOT through `withRouting()`
- **Middleware aliases** in `bootstrap/app.php`: `admin` → `AdminMiddleware`, `locale` → `SetLocale`, `central` → `PreventAccessFromNonCentralDomains`
- **User model** uses Laravel 11+ `#[Fillable]`/`#[Hidden]` PHP attributes. **Admin model** uses traditional `$fillable`/`$hidden` arrays
- **`Admin::getAllPermissions()`** calls `app(\App\Services\Menu::class)` — **class does not exist** (dead `$menu` variable, code continues via `config('menu.groups')` but throws `BindingResolutionException`)

### Onboarding

- **Route**: `GET /register` and `GET /login` both redirect to `/onboarding` (no separate register page)
- **POST /register** removed — all registration through `OnboardingController@store`
- **Flow**: Single-step Alpine.js form → validate all → `Tenant::create()` + `Domain::create()` → `$tenant->run()` creates User + BusinessSetting → cross-domain auto-login via one-time token in `remember_token` → redirect to `{subdomain}.smm.test/{adminPrefix}/dashboard`
- **Auto-login token**: `Str::random(64)` stored as Hash in `remember_token`, consumed once at `/auto-login?email=...&token=...`
- **On tenant subdomains**, register/onboarding GET/POST redirect to `/` (storefront)

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
- **PHP 8.4** in Dockerfile (`php:8.4-cli`), composer.json requires `^8.3`

### Frontend

- Tailwind CSS v4 via `@tailwindcss/vite` plugin (no `tailwind.config.js` — config in CSS)
- **Public layouts**: Tailwind CDN + Alpine.js (not Vite)
- **Tenant/Admin**: Tailwind CDN (dashboard sidebar layout)
- **React Storefront**: `resources/storefront/` — React SPA with Vite + Tailwind CSS v3 (has own `tailwind.config.js`)
- Bengali font: Hind Siliguri (Google Fonts)
- `app.js` is empty — no JS bundled via Vite yet
- **Vite version mismatch**: Main app uses Vite 8.x (`package.json`), storefront uses Vite 5.x (`resources/storefront/package.json`) — separate `node_modules` needed

### React Storefront

- **Location**: `resources/storefront/` — React SPA with Vite + Tailwind CSS v3
- **Theme System**: 2 pre-built themes (modern, classic) hardcoded in `ThemeController::THEMES` array. No themes table.
- **Database**: `storefront_settings` (one row per tenant, NO user_id) stores theme_slug + overrides + layout/contact/social/footer settings. `storefront_banners` stores hero banners with sort_order.
- **Theme Resolution**: Default CSS vars → Preset theme → Tenant overrides → Custom CSS
- **Hot-Swap**: CSS variables on `<html>` — no page reload needed for theme changes
- **FOWT Prevention**: Inject CSS vars inline in Blade `<head>` before React loads
- **API Routes** (`routes/api.php`): `/api/storefront/{config,home,products,products/{slug},categories,brands,featured}`, `/api/themes`
- **Storefront catch-all** (`routes/tenant.php`): LAST route — `GET /{path?}` with optional param serves Blade view with React SPA. Registered AFTER dashboard/auth/storefront-settings routes.
- **Theme Customizer**: `/{adminPrefix}/storefront-settings` — 3 tabs: Theme Selection, General Settings, Banner Management
- **Theme colors**: Include `header_text` for contrast — dark headers get white text, white headers get dark text.
- **Build**: `cd resources/storefront && npx vite build` → outputs to `public/storefront/` (NOT `public/build/`)
- **Asset loading**: `@vite` directive does NOT work for storefront (different output dir). Blade template uses manifest.json first, then glob fallback to find `public/storefront/assets/index-*.js` and `index-*.css`.
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
- **Routes**: `routes/web.php` (central), `routes/tenant.php` (per-tenant), `routes/api.php` (storefront API), `routes/admin.php` (central admin panel)
- **Controllers**: `DashboardController`, `ProductController`, `AttributeTemplateController`, `ThemeController` (hardcoded themes API), `StorefrontController` (serves Blade/React SPA), `StorefrontApiController` (public API), `StorefrontSettingsController` (admin CRUD for settings/banners)
- **Middleware**: `app/Http/Middleware/PreventAccessFromNonCentralDomains.php`, `app/Http/Middleware/SetLocale.php`, `app/Http/Middleware/AdminMiddleware.php`

## Database

Schema source of truth: migration files in `database/migrations/` (landlord) and `database/migrations/tenant/`.

**Landlord** (`socialboost`): `tenants` (string PK subdomain, `data` JSON), `domains`, `admins`, `admin_user_permissions`, `ai_system_prompts`, `business_categories` (with `extra_fields` JSON), `cache`, `jobs`, `sessions`

**Tenant** (`{subdomain}_socialboost`): `users`, `facebook_settings`, `ai_settings`, `conversations`, `messages`, `categories`, `attribute_templates`, `attribute_options`, `brands`, `products`, `product_attribute_values`, `product_variants`, `product_images`, `variant_images`, `warehouses`, `stock_movements`, `stock_transfers`, `inventory_alerts`, `business_settings`, `storefront_settings`, `storefront_banners`

**Seeders**: `AdminSeeder` (`admin@socialboost.com` / `Admin@123456`, super_admin), `BusinessCategorySeeder` (8 categories with extra_fields JSON), `AttributeTemplateSeeder` (8 global variant options + category-specific extra field templates)

## Gotchas

- **`.env.example` is incomplete** — missing `GROQ_MODEL`, `GEMINI_MODEL`, `FACEBOOK_CLIENT_ID`, `FACEBOOK_CLIENT_SECRET`, `APP_DOMAIN`. Has `KILO_MODEL` which `.env` doesn't use. DB defaults to PostgreSQL, queue/cache to `database` — actual `.env` uses MySQL, Redis.
- **`.env.example` defaults to PostgreSQL** — actual `.env` uses MySQL
- **`app.js` is empty** — no JS bundled via Vite yet
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
- **CLIP server URL**: `http://localhost:8089` (local) vs `http://clip-server:8089` (Docker)
- **`TenantCouldNotBeIdentifiedOnDomainException`** caught globally in `bootstrap/app.php` → returns 404.
- **Facebook client secrets in `.env`** are hardcoded — NOT in `.env.example`. Will need re-adding after fresh clone.

# Agent Instructions

Always reply in Banglish (Bengali written in English letters).
No matter what language the user writes in, always respond in Banglish.
Example: "Ei function ta fix korte hobe, karon ekhane error ache."

Always follow best practices for coding architecture (SOLID, DRY, KISS, YAGNI).
Follow Laravel conventions and the conventions of the frameworks/libraries used.
