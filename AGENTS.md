# AGENTS.md

## Quick Start

```bash
composer setup       # install → .env → key → migrate → npm install --ignore-scripts → vite build
composer dev         # artisan serve + queue:listen + pail + vite (concurrently)
composer test        # config:clear + artisan test (SQLite :memory:)
php artisan tenants:migrate   # tenant DBs only — NOT in composer setup
php artisan tenants:seed      # seed tenant DBs
./vendor/bin/pint             # format PHP
```

`config:clear` required before `artisan test` (already in `composer test`). `composer setup` copies `.env.example` → `.env` only if `.env` missing. Root `.npmrc` sets `ignore-scripts=true` — `postinstall` won't run for root deps.

## Architecture

**Laravel 13 + stancl/tenancy v3** — DB-per-tenant multi-tenant SaaS, AI Facebook comment reply.

| Layer | Location | Stack |
|---|---|---|
| Backend | `app/`, `routes/` | PHP 8.4, Laravel 13, stancl/tenancy, Horizon (Redis), MySQL |
| Admin UI | Blade + Alpine.js | Tailwind v4 (`@tailwindcss/vite`), root Vite `^8.0` |
| Storefront SPA | `resources/storefront/` | React 18, React Router 6, Vite `^5.0`, Tailwind v3 (own PostCSS) |
| CLIP Server | `clip-server/` | Python FastAPI, OpenAI CLIP (offline image recognition) |

## Routes (Load Order)

1. `routes/web.php` — landing pages, onboarding, **webhooks** (Facebook/Zernio, CSRF-exempt)
2. `routes/api.php` — storefront `/api/storefront/*` and `/api/themes/*` (tenant-scoped, public via domain middleware)
3. `routes/console.php` — Artisan commands
4. `routes/admin.php` — central admin `/rootadmin/*` (auth guard `admin`, landlord DB). Loaded via `bootstrap/app.php` `then:` callback.
5. `routes/tenant.php` — per-tenant dashboard, inventory, Facebook OAuth, **storefront catch-all LAST** (loaded by tenancy package)

`tenant.php` defines named routes (`login`, `register`, `logout`). Central routes deliberately leave these unnamed — `withRouting()` loads `web.php` AFTER `tenant.php`; a name conflict would overwrite the tenant route.

**Webhooks** (`/webhook/*`, `/facebook/zernio/test-webhook`) CSRF-exempt (`bootstrap/app.php:28-31`), no tenancy middleware — Facebook/Zernio call via ngrok tunnel.

## Tenancy

- **DB naming**: `{tenant_id}_socialboost` (suffix only, no prefix) — `config/tenancy.php:56-57`
- **Central domains**: `127.0.0.1`, `localhost`, `smm.test`, `socialboost.com`, `www.socialboost.com`
- **Tenant admin prefix**: `ADMIN_PANEL_PREFIX` env var (default `ax7k9m`) — accessible at `/{prefix}/*`
- **Central admin prefix**: hardcoded `/rootadmin`
- **Dual auth guards**: `web` (User, tenant DB) + `admin` (Admin, landlord DB)
- **`public` disk NOT tenant-aware** — use `tenant_asset()` not `asset()` for tenant assets (`config/tenancy.php:141` `asset_helper_tenancy => false`)
- **`locale` middleware** on all tenant routes (sets locale from user DB column)
- **`central` middleware** on central-only routes (prevents access on tenant subdomains)

## Storefront SPA (`resources/storefront/`)

- **Own `node_modules/`** — install with `cd resources/storefront && npm install`
- **Build**: `npx vite build` → `public/storefront/` (manifest enabled)
- **Watch**: `npm run watch` — auto-rebuild on change (no HMR on Laravel dev server)
- **Dev proxy**: `vite.config.js` proxies `/api` → `http://localhost:8000`
- **Laravel serves** catch-all `/{path?}` via `StorefrontController` → Blade → `@vite` directive (LAST route in `tenant.php`)
- **Themes**: `resources/storefront/src/themes/` lazy-loaded by slug; `clothing-fashion` (default) and `classic`
- **Tailwind v3** with CSS custom properties for theming
- **Cart**: client-side React Context (localStorage) — no backend cart API yet
- **Implemented pages**: Home, Products, ProductDetail, Category, Brand, Cart, Checkout, Auth (login/register), Dashboard (orders, wishlist, settings, theme editor)

## Docker

```bash
docker compose up -d --build
docker exec laravel-app php artisan <command>
```

**7 services**: app(8000), mysql(3307), node(5173), redis(6379), phpmyadmin(8080), worker(Horizon via Supervisor), clip-server(8089).

- `docker-entrypoint.sh`: waits for MySQL → `composer install --no-dev` → `npm install` → `key:generate` → `migrate` → `artisan serve --host=0.0.0.0 --port=8000`
- Worker runs Supervisor + Horizon (`docker/worker-entrypoint.sh` + `docker/supervisord.conf`)

## Queue / Horizon

- **Driver**: Redis (production), `sync` (testing — `phpunit.xml`)
- **Named queues** (priority): `facebook` > `high` > `default` > `low`
- **Jobs**: `SendAiReplyJob`, `AnalyzeProductImageJob`, `AnalyzeVariantImageJob`, `ProcessImageBatch`, `SyncCategoryAttributeTemplates`
- **SyncCategoryAttributeTemplates** syncs `BusinessCategory.extra_fields` JSON → `attribute_templates` in ALL tenant DBs (runs on BusinessCategory created/updated)
- Horizon dashboard at `/horizon` (local) or configured `HORIZON_PATH`

## Key Services (`app/Services/`)

| Service | Purpose |
|---|---|
| `AiChatService` | AI reply generation (Groq/Cerebras/Gemini, per-tenant config) |
| `ClipService` | CLIP image matching (posts to `clip-server:8089`) |
| `ZernioService` | Zernio social media API v1 |
| `AudioTranscriptionService` | Audio→text for voice messages |

## Inventory (`app/Http/Controllers/Dashboard/`)

Products, variants, categories, brands, attribute templates, warehouses, stock movements, transfers, stock transfers. **No FormRequest classes** — validation inline in controllers.

**Known bugs** (see `INVENTORY_REVIEW.txt`): variant stock not synced to parent, stock ops lack DB transactions, stale attribute values on edit, low-stock alert ignores variants, subcategory assignment broken on create.

## Tests

Minimal — 2 default Laravel example tests (`tests/Feature/ExampleTest.php`, `tests/Unit/ExampleTest.php`). No tenant-specific tests. Run with `composer test`.

## Gotchas

- `.env` uses MySQL+Redis; `.env.example` defaults to PostgreSQL + database-backed queue/cache/session
- `.env` has duplicate `QUEUE_CONNECTION=redis` (lines 39 and 50) — second wins
- Root Vite = Tailwind v4 (`@tailwindcss/vite`); storefront Vite = Tailwind v3 (PostCSS + `tailwindcss` + `autoprefixer`)
- `TenantCouldNotBeIdentifiedOnDomainException` → 404 (`bootstrap/app.php:38-40`)
- CLIP server has own `venv/` — managed via `clip-server/start.sh` or `start.sh`
- `start.sh` orchestrates local dev: Docker + CLIP server + dnsmasq + Apache proxy + Ngrok + storefront auto-build
- `setup-domain.sh` configures `smm.test` wildcard via dnsmasq + Apache reverse proxy to port 8000

## Planning Documents

`INVENTORY_REVIEW.txt` (bug list + architecture plan), `STOREFRONT_PLAN.txt`, `PRODUCT_CATEGORY_FIELDS_PLAN.txt`. Consult these before modifying inventory or storefront — they capture known gaps and future work.
