# AGENTS.md

**Always respond in Banglish** (Bengali + English mixed) unless asked otherwise.

## Quick Start

```bash
composer setup       # install → .env → key → migrate → npm install (root) → vite build (admin)
composer dev         # artisan serve + queue:listen + pail + vite (concurrently)
composer test        # config:clear + artisan test (SQLite :memory:)
php artisan tenants:migrate   # tenant DBs only
php artisan tenants:seed      # seed tenant DBs
./vendor/bin/pint             # format PHP
```

`config:clear` required before `artisan test` (composer test handles it). Root `.npmrc` sets `ignore-scripts=true`. `composer setup` only copies `.env.example`→`.env` if `.env` missing.

**Storefront has own npm**: `cd resources/storefront && npm install && npm run build` (output → `public/storefront/`, separate Vite 5 + React 18 build). Not covered by `composer setup` — run separately.

## Project Structure

| Layer | Location | Stack |
|---|---|---|
| Backend | `app/`, `routes/` | PHP 8.4, Laravel 13.8+, stancl/tenancy v3, Horizon (Redis), MySQL |
| Admin UI | Blade + Alpine.js | Tailwind v4 (`@tailwindcss/vite`), root Vite 8 |
| Storefront SPA | `resources/storefront/` | React 18, React Router 6, Vite 5, Tailwind v3 (own PostCSS) |
| CLIP Server | `clip-server/` | Python FastAPI, OpenAI CLIP (offline) |

## Routes (Load Order)

1. `routes/web.php` — landing pages, onboarding, **webhooks** (Facebook/Zernio, CSRF-exempt)
2. `routes/api.php` — `/api/storefront/*` and `/api/themes/*` (tenant-scoped via domain middleware), `/api/editor/*` (theme editor PUT endpoints)
3. `routes/console.php` — Artisan commands
4. `routes/admin.php` — central admin `/rootadmin/*` (auth guard `admin`, landlord DB). Loaded via `bootstrap/app.php` `then:` callback.
5. `routes/tenant.php` — per-tenant dashboard, inventory, Facebook OAuth, **storefront catch-all LAST** (loaded by `TenancyServiceProvider` via `booted()` callback)

`tenant.php` defines named routes (`login`, `register`, `logout`). Central routes deliberately leave these unnamed — `withRouting()` loads `web.php` AFTER `tenant.php`; a name conflict would overwrite the tenant route.

**Webhooks** CSRF-exempt (`bootstrap/app.php:28-31`), no tenancy middleware — Facebook/Zernio call via ngrok tunnel.

## Tenancy

- **DB naming**: `{tenant_id}_socialboost` (prefix `''`, suffix `'_socialboost'`) — `config/tenancy.php:56-57`
- **Central domains**: `127.0.0.1`, `localhost`, `smm.test`, `socialboost.com`, `www.socialboost.com`
- **Tenant admin prefix**: `ADMIN_PANEL_PREFIX` env var (default `ax7k9m`)
- **Central admin prefix**: hardcoded `/rootadmin`
- **Dual auth guards**: `web` (User, tenant DB) + `admin` (Admin, landlord DB)
- **`public` disk NOT tenant-aware** — use `tenant_asset()` not `asset()` (`config/tenancy.php:141` `asset_helper_tenancy => false`)
- **`locale` middleware** on all tenant routes (sets locale from user DB column)
- **`central` middleware** on central-only routes (prevents access on tenant subdomains)
- **`TenantCouldNotBeIdentifiedOnDomainException` → 404** (`bootstrap/app.php:38-40`)
- **Tenant DB auto-created** on tenant creation — `TenancyServiceProvider` pipelines `CreateDatabase` + `MigrateDatabase` jobs

## Storefront SPA

- Own `node_modules/` + own `vite.config.js` (Vite 5, no laravel-vite-plugin)
- **Build**: `cd resources/storefront && npm run build` → `public/storefront/` (manifest enabled)
- **Watch**: `npm run watch` — auto-rebuild on change (no HMR)
- **Dev proxy**: `vite.config.js` proxies `/api` → `http://localhost:8000`
- **Themes**: lazy-loaded via `resources/storefront/src/themes/index.js`; `clothing-fashion` (default) and `classic` (both registered but dirs may not exist yet)
- **Cart**: client-side React Context (localStorage) — no backend cart API
- **Editor mode**: `?editor=true` URL param toggles `EditableSection` overlays
- **`sections_data`** JSON column on `StorefrontSettings` stores editor state (categories, all_categories, banners, section_titles)

## Docker

```bash
docker compose up -d --build
docker exec laravel-app php artisan <command>
```

**7 services**: app(8000), mysql(3307), node(5173), redis(6379), phpmyadmin(8080), worker(Horizon via Supervisor), clip-server(8089).

- `docker-entrypoint.sh`: waits for MySQL → `composer install --no-dev` → `npm install` (root only) → `key:generate` → `migrate` → `artisan serve --host=0.0.0.0 --port=8000`
- Node service runs `npm install && npm run dev -- --host 0.0.0.0`
- **Storefront not auto-built in Docker** — build manually
- Worker image reuses `socialmediamanager-app:latest`; runs Supervisor + Horizon (`docker/worker-entrypoint.sh` + `docker/supervisord.conf`)
- CLIP server: Dockerfile in `clip-server/`, port 8089, persists model cache to `clip-models` volume

## Queue / Horizon

- **Driver**: Redis (production), `sync` (testing — `phpunit.xml`)
- **Named queues** (priority): `facebook` > `high` > `default` > `low`
- **Jobs**: `SendAiReplyJob`, `AnalyzeProductImageJob`, `AnalyzeVariantImageJob`, `ProcessImageBatch`, `SyncCategoryAttributeTemplates`
- `SyncCategoryAttributeTemplates` syncs `BusinessCategory.extra_fields` JSON → `attribute_templates` in ALL tenant DBs (runs on BusinessCategory created/updated)
- Horizon dashboard at `/horizon` (local) or configured `HORIZON_PATH`
- Horizon supervisor config: `docker/supervisord.conf` (3 tries, 1800s timeout, 5-30 procs)

## Key Services (`app/Services/`)

| Service | Purpose |
|---|---|
| `AiChatService` | AI reply generation (Groq/Cerebras/Gemini, per-tenant config) |
| `ClipService` | CLIP image matching (posts to `clip-server:8089`) |
| `ZernioService` | Zernio social media API v1 |
| `AudioTranscriptionService` | Audio→text for voice messages |

## Inventory (`app/Http/Controllers/Dashboard/`)

Products, variants, categories, brands, attribute templates, warehouses, stock movements, transfers. **No FormRequest classes** — validation inline in controllers.

**Known bugs** (see `INVENTORY_REVIEW.txt`): variant stock not synced to parent, stock ops lack DB transactions, stale attribute values on edit, low-stock alert ignores variants, subcategory assignment broken on create.

## Tests

Minimal — 2 Laravel examples (`tests/Feature/ExampleTest.php`, `tests/Unit/ExampleTest.php`). No tenant-specific tests. Run with `composer test` (SQLite :memory:, `QUEUE_CONNECTION=sync`).

## Formatting & Editor Config

- `.editorconfig`: 4-space indent, LF endings (2-space for `.yml`/`.yaml`, 4-space for compose files)
- PHP formatting: `./vendor/bin/pint` (Laravel Pint, no custom config file)
- No Prettier, ESLint, or stylelint configs exist

## Gotchas

- `.env` uses MySQL+Redis; `.env.example` defaults to PostgreSQL + database-backed queue/cache/session
- `.env` has duplicate `QUEUE_CONNECTION=redis` entries — last one wins
- `.env` `APP_NAME="Get ERP Store"` — not the repo name
- Root Vite = Tailwind v4 (`@tailwindcss/vite`); storefront Vite = Tailwind v3 (PostCSS + `tailwindcss` + `autoprefixer`)
- `start.sh` orchestrates local dev: Docker + CLIP server + dnsmasq + Apache proxy + Ngrok + storefront auto-build
- `setup-domain.sh` configures `smm.test` wildcard via dnsmasq + Apache reverse proxy to port 8000
- CLIP server has own `venv/` — managed via `clip-server/start.sh` or `start.sh`

## Planning Documents

`INVENTORY_REVIEW.txt` (bug list + architecture plan), `STOREFRONT_PLAN.txt`, `PRODUCT_CATEGORY_FIELDS_PLAN.txt`. Consult these before modifying inventory or storefront — they capture known gaps and future work.
