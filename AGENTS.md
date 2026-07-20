# AGENTS.md

## Quick Start

```bash
composer setup     # install → .env → key → migrate → npm install → vite build
composer dev       # artisan serve + queue:listen + pail + vite (concurrently)
composer test      # config:clear + artisan test (SQLite :memory:)
php artisan tenants:migrate   # run tenant DB migrations
php artisan tenants:seed      # seed tenant DBs
```

## Architecture

**Laravel 13 + stancl/tenancy v3** — DB-per-tenant SaaS with AI-powered Facebook comment reply.

| Layer | Location | Stack |
|---|---|---|
| Backend | `app/`, `routes/` | PHP 8.4, Laravel 13, stancl/tenancy, Horizon (Redis), MySQL |
| Admin UI | Blade + Alpine.js | Tailwind v4 (`@tailwindcss/vite`), served by root Vite |
| Storefront SPA | `resources/storefront/` | React 18, React Router 6, Vite 5, Tailwind v3 (own PostCSS config) |
| CLIP Server | `clip-server/` | Python FastAPI, OpenAI CLIP model (local/offline image recognition) |

## Storefront SPA (`resources/storefront/`)

- **Own `node_modules/`** — separate from root, install with `cd resources/storefront && npm install`
- **Build**: `npx vite build` — output goes to `public/storefront/`
- **Watch**: `npm run watch` — auto-rebuild on change (no HMR on Laravel dev server)
- **Laravel serves** at `/{path?}` catch-all via `StorefrontController` → Blade → `@vite` directive
- **Theme system**: `src/themes/` — lazy-loaded by slug; `clothing-fashion` and `classic` exist
- **`App.jsx`** fetches `/api/storefront/config` for theme/tenant config (falls back to defaults)
- **DashboardLayout** routes: `/dashboard`, `/dashboard/orders`, `/tracking`, `/wishlist`, `/addresses`, `/settings`

## Routes (Load Order Matters)

1. `web.php` — central landing pages, onboarding wizard, webhooks (Facebook/Zernio)
2. `admin.php` — central admin panel at `/rootadmin/*` (auth guard: `admin`, DB: landlord)
3. `tenant.php` — per-tenant routes: auth, dashboard, inventory, Facebook OAuth, **storefront catch-all (LAST)**

### Tenancy

- **DB naming**: `{prefix}{tenant_id}_socialboost` (e.g. `tenant1_socialboost`)
- **Central domains**: `127.0.0.1`, `localhost`, `smm.test`, `socialboost.com`, `www.socialboost.com`
- **Admin panel prefix**: `config('app.admin_panel_prefix', 'ax7k9m')` — `.env` uses `supermaster`
- **Dual auth guards**: `web` (User, tenant DB) + `admin` (Admin, landlord DB)
- **Users table → tenant DB only**
- `tenant.php` registers login/logout names — central routes intentionally avoid name conflicts
- Route name conflict: `withRouting()` loads `web.php` AFTER `tenant.php` — central names overwrite tenant names

## Docker

```bash
docker compose up -d --build
docker exec laravel-app php artisan <command>
```

**7 services**: app(8000), mysql(3307), node(5173), redis(6379), phpmyadmin(8080), worker(Horizon via Supervisor), clip-server(8089).

## Key Services (`app/Services/`)

| Service | Purpose |
|---|---|
| `AiChatService` | AI reply generation (Groq/Cerebras/Gemini configurable per tenant) |
| `ClipService` | CLIP image matching client (posts to `clip-server:8089`) |
| `ZernioService` | Zernio social media API v1 client |
| `AudioTranscriptionService` | Audio→text for voice messages |

## Inventory System

- Products, variants (JSON attributes), categories, brands, attribute templates, warehouses, stock movements, low-stock alerts
- All tenant DB tables (under `app/Http/Controllers/Dashboard/`)
- **Known bugs** (per `INVENTORY_REVIEW.txt`): variant stock not synced to parent product, stock operations lack DB transactions, stale attribute values on edit

## Gotchas

- `.env` uses MySQL+Redis; `.env.example` defaults to PostgreSQL+`database` queue/cache
- Root Vite uses **Tailwind v4** (`@tailwindcss/vite`); storefront Vite uses **Tailwind v3** (PostCSS)
- Root `.npmrc` sets `ignore-scripts=true` — `postinstall` hooks won't run for root deps
- Storefront edits require `npx vite build` — no HMR on Laravel dev server
- `TenantCouldNotBeIdentifiedOnDomainException` → 404 (subdomain not recognized)
- PHP 8.4: `a ? b : c ? d : e` (unparenthesized ternary) forbidden
- `composer test` → `config:clear` uses `@no_additional_args` flag, then `artisan test` (uses SQLite `:memory:`)
- `composer setup` copies `.env` only if it doesn't exist — does **not** overwrite
- `start.sh` manages local dev: Docker + CLIP server + dnsmasq + Apache proxy + Ngrok + storefront auto-build
- `setup-domain.sh` configures `smm.test` with wildcard dnsmasq + Apache reverse proxy to port 8000
- Storefront static data is for theme dev only — production uses API calls to `/api/storefront/*`
