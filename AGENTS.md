# AGENTS.md

## Commands

```bash
composer setup     # install → .env → key → migrate → npm install → vite build
composer dev       # artisan serve + queue:listen + pail + vite (concurrently)
composer test      # config:clear + artisan test (SQLite :memory:)

php artisan tenants:migrate   # migrate all tenant DBs
php artisan tenants:seed      # seed all tenant DBs

cd resources/storefront
npm run watch     # auto-rebuild React SPA on change → public/storefront/
npx vite build    # build once
```

## Multi-Tenancy

- **DB-per-tenant**: Landlord `socialboost` / Tenant `{subdomain}_socialboost`
- **Users table → tenant DB only** — never from central routes
- **Tenant migrations**: `database/migrations/tenant/`
- **Central domains** (`config/tenancy.php`): `127.0.0.1`, `localhost`, `smm.test`, `socialboost.com`, `www.socialboost.com`

## Route Order (CRITICAL)

Load order: `web.php` (central, lazy via `withRouting`) → `admin.php` (central, `then:` callback) → `tenant.php` (per-tenant, `TenancyServiceProvider::booted`).

**Central routes use NO route names** that conflict with tenant names (`login`, `register`, `logout`, `dashboard`, `settings`, `leads`, `reports`).

**Tenant route order** in `tenant.php`:
1. Language switch (no auth, outside prefix)
2. Auth routes inside `{adminPrefix}` (no auth middleware)
3. Dashboard/CRUD routes inside `{adminPrefix}` (auth middleware)
4. Block register/onboarding redirects
5. **Storefront catch-all** `GET /{path?}` (last, no auth)

If catch-all comes first, it swallows all dashboard/login routes.

## Admin Panel Prefix

- `.env.example`: `ADMIN_PANEL_PREFIX=ax7k9m`
- `.env`: `ADMIN_PANEL_PREFIX=supermaster`
- Read via `config('app.admin_panel_prefix', 'ax7k9m')`
- Tenant URLs: `{subdomain}.smm.test/supermaster/{login,dashboard,storefront-settings,...}`
- Central admin: `smm.test/rootadmin/{login,dashboard,...}` — `admin` guard, landlord DB

## Auth

- **Dual guards**: `web` (User, tenant DB) + `admin` (Admin, landlord DB)
- **User** uses PHP 8 `#[Fillable]`/`#[Hidden]` attributes; **Admin** uses traditional arrays
- **`Admin::getAllPermissions()`** calls `app(\App\Services\Menu::class)` — that class does NOT exist (throws for super_admin; non-super path works)
- Middleware aliases: `admin` → `AdminMiddleware`, `locale` → `SetLocale`, `central` → `PreventAccessFromNonCentralDomains`

## BusinessSetup

Landlord DB singleton: `BusinessSetup::getActive()` = `firstOrCreate([])`. Globally available in all Blade views as `$businessSetup`. Logo at `storage/app/public/business-logos/`.

## Onboarding

Single Alpine.js form → `Tenant::create()` + `Domain::create()` → `$tenant->run()` creates User/BusinessSetting → cross-domain auto-login via hashed one-time token → redirect to `{subdomain}.smm.test/supermaster/dashboard`.

## AI & Facebook

- **3-tier fallback**: Groq → Cerebras → Gemini (key rotation within each)
- **CLIP server**: `http://localhost:8089` (local) / `http://clip-server:8089` (Docker)
- **Queue**: Redis via Horizon (Docker worker container, Supervisor)
- **Webhook**: `/webhook/facebook` (central, CSRF-exempt). Iterates ALL tenants to find matching `page_id` (O(n)).
- **FB OAuth**: requires HTTPS (ngrok tunnel)

## Docker

```bash
docker compose up -d --build
docker exec laravel-app php artisan <command>
```

7 services: app(8000), mysql(3307), node(5173), redis(6379), phpmyadmin(8080), worker(Supervisor→Horizon), clip-server(8089). Worker uses pre-built `socialmediamanager-app:latest` image. `.env` uses `DB_HOST=mysql`; `.env.example` defaults to PostgreSQL.

## Frontend

- **Main app**: Tailwind v4 (CSS-based, no config file), Vite 8.x
- **Storefront** (`resources/storefront/`): React SPA, Vite 5.x + Tailwind v3 (own config). Separate `node_modules`.
- **`.npmrc`**: `ignore-scripts=true` — postinstall skipped
- **Storefront uploads**: NOT tenant-isolated (public disk not in tenancy `disks`)
- **`npm run watch`** auto-rebuilds; output → `public/storefront/`

## Gotchas

- `.env.example` missing: `FACEBOOK_CLIENT_ID`, `FACEBOOK_CLIENT_SECRET`, `GROQ_MODEL`, `GEMINI_MODEL`, `APP_DOMAIN`. Has unused `KILO_MODEL`.
- `BusinessCategory` in landlord DB → always `::on('mysql')->find(...)`
- `attribute_templates.category_id` → tenant `categories.id`, NOT `business_categories.id`

- PHP 8.4: unparenthesized ternary `a ? b : c ? d : e` forbidden
- Route name conflict: `withRouting()` loads web.php AFTER tenant.php — central names overwrite tenant names
- `TenantCouldNotBeIdentifiedOnDomainException` → 404
- `storefront_banners` table/model/API exist, admin UI removed
- `footer_logo` field exists with no upload UI
- Product images API returns raw path; logo/favicon return full URL via `Storage::url()`
- `.env.example` has PostgreSQL + `database` queue/cache; actual `.env` uses MySQL + Redis
