# AGENTS.md

## Quick Start

```bash
composer setup       # install → .env → key → migrate → npm install → vite build
composer dev         # artisan serve + queue:listen + pail + vite (concurrently)
composer test        # config:clear + artisan test (SQLite :memory:)
php artisan tenants:migrate   # run tenant DB migrations (separate step!)
php artisan tenants:seed      # seed tenant DBs
./vendor/bin/pint             # format PHP code (laravel/pint)
```

`config:clear` must run before `artisan test` to avoid stale config cache. `composer setup` copies `.env.example` only if `.env` doesn't exist — does **not** overwrite.

## Architecture

**Laravel 13 + stancl/tenancy v3** — DB-per-tenant SaaS with AI-powered Facebook comment reply.

| Layer | Location | Stack |
|---|---|---|
| Backend | `app/`, `routes/` | PHP 8.4, Laravel 13, stancl/tenancy, Horizon (Redis), MySQL |
| Admin UI | Blade + Alpine.js | Tailwind v4 (`@tailwindcss/vite`), served by root Vite |
| Storefront SPA | `resources/storefront/` | React 18, React Router 6, Vite 5, Tailwind v3 (own PostCSS config) |
| CLIP Server | `clip-server/` | Python FastAPI, OpenAI CLIP model (local/offline image recognition) |

## Routes (Load Order Matters)

1. `routes/web.php` — central landing pages, onboarding wizard, webhooks (Facebook/Zernio)
2. `routes/admin.php` — central admin panel, auth guard `admin`, DB landlord
3. `routes/tenant.php` — per-tenant: auth, dashboard, inventory, Facebook OAuth, **storefront catch-all (LAST)**

**Naming convention**: `tenant.php` defines named routes (`login`, `register`, `logout`). Central routes deliberately leave these unnamed — `withRouting()` loads `web.php` AFTER `tenant.php`, so a name conflict would overwrite the tenant route.

**Webhooks** (`/webhook/facebook`, `/webhook/zernio`) are accessible from any domain (no tenancy middleware) — Facebook/Zernio call via ngrok tunnel.

## Tenancy

- **DB naming**: `{prefix}{tenant_id}_socialboost` (e.g. `tenant1_socialboost`)
- **Central domains**: `127.0.0.1`, `localhost`, `smm.test`, `socialboost.com`, `www.socialboost.com`
- **Admin panel prefix**: `ADMIN_PANEL_PREFIX` env var, default `ax7k9m` — accessible at `/{prefix}/*`
- **Dual auth guards**: `web` (User, tenant DB) + `admin` (Admin, landlord DB)
- **Users table → tenant DB only**. Admins table → landlord DB.
- **`public` disk is NOT tenant-aware** — shared across tenants. `asset_helper_tenancy` is disabled — use `tenant_asset()` for tenant-specific assets.
- **No FormRequest classes** — validation is inline in controllers.

## Storefront SPA (`resources/storefront/`)

- **Own `node_modules/`** — install with `cd resources/storefront && npm install`
- **Build**: `npx vite build` → output to `public/storefront/`
- **Build watch**: `npm run watch` — auto-rebuild on change (no HMR on Laravel dev server)
- **Dev proxy**: `vite.config.js` proxies `/api` → `http://localhost:8000`
- **Laravel serves** at `/{path?}` catch-all via `StorefrontController` → Blade → `@vite` directive (LAST route in `tenant.php`)
- **Themes**: `src/themes/` lazy-loaded by slug; `clothing-fashion` and `classic` exist
- **`App.jsx`** fetches `/api/storefront/config` for theme/tenant config (falls back to defaults)
- **Static data** in storefront is for theme dev only — production uses `/api/storefront/*`

## Docker

```bash
docker compose up -d --build
docker exec laravel-app php artisan <command>
```

**7 services**: app(8000), mysql(3307), node(5173), redis(6379), phpmyadmin(8080), worker(Horizon via Supervisor), clip-server(8089).

Docker entrypoint runs `composer install --no-dev` — no dev dependencies in containers.

## Queue / Horizon

- **Driver**: Redis (production), `sync` (testing — set in `phpunit.xml`)
- **Named queues** (in priority order): `facebook`, `high`, `default`, `low`
- **Jobs**: `SendAiReplyJob`, `AnalyzeProductImageJob`, `AnalyzeVariantImageJob`, `ProcessImageBatch`, `SyncCategoryAttributeTemplates`
- Horizon dashboard at `/horizon` (local) or configured `HORIZON_PATH`

## Key Services (`app/Services/`)

| Service | Purpose |
|---|---|
| `AiChatService` | AI reply generation (Groq/Cerebras/Gemini, per-tenant config) |
| `ClipService` | CLIP image matching client (posts to `clip-server:8089`) |
| `ZernioService` | Zernio social media API v1 client |
| `AudioTranscriptionService` | Audio→text for voice messages |

## Inventory

- Products, variants (JSON attributes), categories, brands, attribute templates, warehouses, stock movements, stock transfers, low-stock alerts
- **Post-review additions**: `AttributeOption`, `VariantAttributeValue`, `VariantImage`, `StockTransfer` models now exist
- **Known issues** (see `INVENTORY_REVIEW.txt` for full analysis): variant stock not synced to parent, stock ops lack DB transactions, stale attribute values on edit, low-stock alert ignores variants
- Tenant DB tables under `app/Http/Controllers/Dashboard/`

## Gotchas

- `.env` uses MySQL+Redis; `.env.example` defaults to PostgreSQL+`database` queue/cache
- Root `.npmrc` sets `ignore-scripts=true` — `postinstall` hooks won't run for root deps
- Root Vite uses **Tailwind v4** (`@tailwindcss/vite`); storefront Vite uses **Tailwind v3** (PostCSS)
- `TenantCouldNotBeIdentifiedOnDomainException` → 404 (subdomain not recognized)
- PHP 8.4: `a ? b : c ? d : e` (unparenthesized ternary) forbidden
- Multi-language support: `locale` middleware, `LanguageController`, `locale` column on users
- CLIP server has its own `venv/` — managed via `clip-server/start.sh` or `start.sh`
- `start.sh` manages local dev: Docker + CLIP server + dnsmasq + Apache proxy + Ngrok + storefront auto-build
- `setup-domain.sh` configures `smm.test` with wildcard dnsmasq + Apache reverse proxy to port 8000
