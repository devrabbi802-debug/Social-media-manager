# AGENTS.md

## Quick Start

```bash
composer setup       # install → .env → key → migrate → npm install (--ignore-scripts) → vite build
composer dev         # artisan serve + queue:listen + pail + vite (concurrently)
composer test        # config:clear + artisan test (SQLite :memory:)
php artisan tenants:migrate   # separate step — NOT part of composer setup
php artisan tenants:seed      # seed tenant DBs
./vendor/bin/pint             # format PHP code
```

`config:clear` must run before `artisan test` (already in `composer test`). `composer setup` copies `.env.example` only if `.env` doesn't exist.

## Architecture

**Laravel 13 + stancl/tenancy v3** — DB-per-tenant SaaS with AI-powered Facebook comment reply.

| Layer | Location | Stack |
|---|---|---|
| Backend | `app/`, `routes/` | PHP 8.4, Laravel 13, stancl/tenancy, Horizon (Redis), MySQL |
| Admin UI | Blade + Alpine.js | Tailwind v4 (`@tailwindcss/vite`), root Vite `^8.0` |
| Storefront SPA | `resources/storefront/` | React 18, React Router 6, Vite `^5.0`, Tailwind v3 (own PostCSS) |
| CLIP Server | `clip-server/` | Python FastAPI, OpenAI CLIP (offline image recognition) |

## Routes (Load Order)

1. `routes/web.php` — landing pages, onboarding wizard, webhooks (Facebook/Zernio)
2. `routes/admin.php` — central admin panel (`/rootadmin/*`), auth guard `admin`, landlord DB. Loaded via `bootstrap/app.php` `then:` callback.
3. `routes/tenant.php` — per-tenant: auth, dashboard, inventory, Facebook OAuth, **storefront catch-all (LAST)**
4. `routes/api.php` — storefront `/api/storefront/*` and `/api/themes/*` (tenant-scoped, public)
5. `routes/console.php` — Artisan commands

`tenant.php` defines named routes (`login`, `register`, `logout`). Central routes deliberately leave these unnamed — `withRouting()` loads `web.php` AFTER `tenant.php`, a name conflict would overwrite the tenant route.

**Webhooks** (`/webhook/facebook`, `/webhook/zernio`, `/facebook/zernio/test-webhook`) are CSRF-exempt (`bootstrap/app.php:28-31`) and accessible from any domain (no tenancy middleware) — Facebook/Zernio call via ngrok tunnel.

## Tenancy

- **DB naming**: `{tenant_id}_socialboost` (suffix `_socialboost`, no prefix) — `config/tenancy.php:56-57`
- **Central domains** (source of truth): `127.0.0.1`, `localhost`, `smm.test`, `socialboost.com`, `www.socialboost.com`
- **Tenant admin prefix**: `ADMIN_PANEL_PREFIX` env var (default `ax7k9m`) — accessible at `/{prefix}/*`
- **Central admin prefix**: hardcoded `/rootadmin` (separate from tenant prefix)
- **Dual auth guards**: `web` (User, tenant DB) + `admin` (Admin, landlord DB)
- **No FormRequest classes** — validation inline in controllers
- **`public` disk is NOT tenant-aware** — use `tenant_asset()` instead of `asset()` for tenant-specific assets (`config/tenancy.php:141` `asset_helper_tenancy => false`)
- **`locale` middleware** applied to all tenant routes (sets user locale from DB column)
- **`central` middleware** applied to central-only routes (prevents access on tenant subdomains)

## Storefront SPA (`resources/storefront/`)

- **Own `node_modules/`** — install with `cd resources/storefront && npm install`
- **Build**: `npx vite build` → `public/storefront/` (manifest enabled)
- **Watch**: `npm run watch` — auto-rebuild on change (no HMR on Laravel dev server)
- **Dev proxy**: `vite.config.js` proxies `/api` → `http://localhost:8000`
- **Laravel serves** at `/{path?}` catch-all via `StorefrontController` → Blade → `@vite` directive (LAST route in `tenant.php`)
- **Themes**: `resources/storefront/src/themes/` lazy-loaded by slug; `clothing-fashion` (default fallback) and `classic`
- **`App.jsx`** fetches `/api/storefront/config` for theme/tenant config
- **Static data** is for theme dev only — production uses `/api/storefront/*`
- **Tailwind v3** with CSS custom properties for theming (colors, fonts, border-radius sourced from CSS vars)

## Docker

```bash
docker compose up -d --build
docker exec laravel-app php artisan <command>
```

**7 services**: app(8000), mysql(3307), node(5173 — `npm run dev -- --host 0.0.0.0`), redis(6379), phpmyadmin(8080), worker(Horizon via Supervisor), clip-server(8089).

- `docker-entrypoint.sh`: waits for MySQL → `composer install --no-dev` → `npm install` → `key:generate` → `migrate` → `artisan serve --host=0.0.0.0 --port=8000`
- Worker runs Supervisor with Horizon (`docker/worker-entrypoint.sh` + `docker/supervisord.conf`)

## Queue / Horizon

- **Driver**: Redis (production), `sync` (testing — set in `phpunit.xml`)
- **Named queues** (priority order): `facebook`, `high`, `default`, `low`
- **Jobs**: `SendAiReplyJob`, `AnalyzeProductImageJob`, `AnalyzeVariantImageJob`, `ProcessImageBatch`, `SyncCategoryAttributeTemplates`
- Horizon dashboard at `/horizon` (local) or configured `HORIZON_PATH`

## Key Services (`app/Services/`)

| Service | Purpose |
|---|---|
| `AiChatService` | AI reply generation (Groq/Cerebras/Gemini, per-tenant config) |
| `ClipService` | CLIP image matching client (posts to `clip-server:8089`) |
| `ZernioService` | Zernio social media API v1 client |
| `AudioTranscriptionService` | Audio→text for voice messages |

## Inventory (`app/Http/Controllers/Dashboard/`)

- Products, variants, categories, brands, attribute templates, warehouses, stock movements, transfers
- **Known bugs** (see `INVENTORY_REVIEW.txt`): variant stock not synced to parent, stock ops lack DB transactions, stale attribute values on edit, low-stock alert ignores variants

## Gotchas

- `.env` uses MySQL+Redis; `.env.example` defaults to PostgreSQL + database-backed queue/cache/session
- Root `.npmrc` sets `ignore-scripts=true` and `audit=true` — `postinstall` won't run for root deps
- Root Vite = Tailwind v4 (`@tailwindcss/vite`); storefront Vite = Tailwind v3 (PostCSS + `tailwindcss` + `autoprefixer`)
- `TenantCouldNotBeIdentifiedOnDomainException` → 404 (handled in `bootstrap/app.php:38-40`)
- CLIP server has own `venv/` — managed via `clip-server/start.sh` or `start.sh`
- `start.sh` manages local dev: Docker + CLIP server + dnsmasq + Apache proxy + Ngrok + storefront auto-build
- `setup-domain.sh` configures `smm.test` wildcard with dnsmasq + Apache reverse proxy to port 8000

## Planning Documents

Reference docs at repo root: `INVENTORY_REVIEW.txt` (bug list + architecture plan), `STOREFRONT_PLAN.txt`, `PRODUCT_CATEGORY_FIELDS_PLAN.txt`. These capture known gaps and future work — consult before making changes to inventory or storefront.

## Communication

- **Language**: Always Banglish (Bengali + English mix) — user er sathe kotha bolar somoy shudhu Banglish use korte hobe. Full English or full Bengali avoid korte hobe.
