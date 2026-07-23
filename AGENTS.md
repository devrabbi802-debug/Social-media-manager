# AGENTS.md

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

**Storefront has own npm**: `cd resources/storefront && npm install && npm run build` (output → `public/storefront/`, own Vite 5 + React 18). Not covered by `composer setup`.

## Architecture

| Layer | Location | Stack |
|---|---|---|
| Backend | `app/`, `routes/` | PHP 8.4, Laravel 13, stancl/tenancy v3, Horizon (Redis), MySQL |
| Admin UI | Blade + Alpine.js | Tailwind v4 (`@tailwindcss/vite`), root Vite 8 |
| Storefront SPA | `resources/storefront/` | React 18, React Router 6, Vite 5, Tailwind v3 (own PostCSS), Axios |
| CLIP Server | `clip-server/` | Python FastAPI, OpenAI CLIP (offline) |

## Routes (Load Order)

1. `routes/web.php` — landing pages, onboarding, webhooks (CSRF-exempt via `bootstrap/app.php:28-31`)
2. `routes/api.php` — storefront SPA API (tenant-scoped). Groups: `/api/storefront/*` (public), `/api/auth/*` (public + `auth:sanctum` protected), `/api/checkout/*` (public), `/api/customer/*` (`auth:sanctum`), `/api/editor/*` (theme PUT), `/api/themes/*`
3. `routes/console.php` — Artisan commands
4. `routes/admin.php` — central admin `/rootadmin/*` (guard `admin`, landlord DB). Loaded via `bootstrap/app.php` `then:` callback.
5. `routes/tenant.php` — per-tenant dashboard, inventory, Facebook OAuth, **storefront catch-all LAST**. Loaded by `TenancyServiceProvider::mapRoutes()` on `booted`.

`tenant.php` defines named routes (`login`, `register`, `logout`). Central routes deliberately leave these unnamed — `withRouting()` loads `web.php` after `tenant.php`; a name conflict would overwrite.

## Tenancy

- **DB naming**: prefix `''` + tenant_id + suffix `'_socialboost'` (`config/tenancy.php:56-57`)
- **Central domains**: `127.0.0.1`, `localhost`, `smm.test`, `socialboost.com`, `www.socialboost.com`
- **Tenant admin prefix**: `ADMIN_PANEL_PREFIX` env var (default `ax7k9m`)
- **Central admin prefix**: hardcoded `/rootadmin`
- **Dual auth guards**: `web` (User, tenant DB) + `admin` (Admin, landlord DB)
- **Sanctum**: installed for SPA token auth — `personal_access_tokens` table in tenant DB
- **`public` disk NOT tenant-aware** — use `tenant_asset()` not `asset()` (`config/tenancy.php:141` `asset_helper_tenancy => false`)
- **`locale` middleware** on all tenant routes (sets locale from `user.locale` column)
- **`central` middleware** (`PreventAccessFromNonCentralDomains`) on central-only routes
- **`TenantCouldNotBeIdentifiedOnDomainException` → 404** (`bootstrap/app.php:38-40`)
- **Tenant DB auto-created** on creation via `CreateDatabase` + `MigrateDatabase` job pipeline

## Storefront SPA

- Own `node_modules/`, own `vite.config.js` (Vite 5, no laravel-vite-plugin)
- **Build**: `cd resources/storefront && npm run build` → `public/storefront/` (manifest enabled)
- **Watch**: `npm run watch` — auto-rebuild on change (no HMR)
- **Dev proxy**: `vite.config.js` proxies `/api` → `http://localhost:8000`
- **Auth**: Laravel Sanctum token-based. Token stored in `localStorage` as `auth_token`. Auto-attached via Axios interceptor. 401 response clears token and redirects to `/auth`.
- **Themes**: lazy-loaded via `resources/storefront/src/themes/index.js`. 2 registered: `clothing-fashion` (default) and `classic` — both exist at `resources/storefront/src/themes/{slug}/`.
- **Cart**: client-side React Context + `localStorage` persistence (key `storefront_cart`). No backend cart API.
- **Editor mode**: `?editor=true` URL param toggles `EditableSection` overlays for admin theme editing.
- **`sections_data`** JSON column on `StorefrontSettings` stores editor state (banners, categories, section_titles, etc.)
- **Guest checkout**: `POST /api/checkout/place` works without auth. Shipping address optional. No email required.
- **Customer dashboard**: `/dashboard/*` routes protected by `RequireAuth` component. Data fetched from `/api/customer/*`.
- **Shared pages** (not theme-specific): `resources/storefront/src/pages/` — ForgotPassword, ResetPassword

### API Endpoint Groups

| Prefix | Auth | Purpose |
|--------|------|---------|
| `/api/storefront/*` | None | Config, home, products, categories, brands |
| `/api/auth/*` (POST register/login/forgot/reset) | None | Public auth |
| `/api/auth/*` (logout/user/profile/password) | `auth:sanctum` | Authenticated auth |
| `/api/checkout/*` | None | Place order (guest or auth), track order |
| `/api/customer/*` | `auth:sanctum` | Orders, addresses, wishlist, reviews, stats |
| `/api/themes/*` | None | Theme listing |
| `/api/editor/*` | None | Theme editor CRUD (intended for admin) |

## Docker

```bash
docker compose up -d --build
docker exec laravel-app php artisan <command>
```

**7 services**: app(8000), mysql(3307), node(5173), redis(6379), phpmyadmin(8080), worker(Horizon via Supervisor), clip-server(8089).

- `docker-entrypoint.sh`: waits for MySQL → `composer install --no-dev` → `npm install` (root only) → `key:generate` → `migrate` → `octane:start --server=swoole --host=0.0.0.0 --port=8000`
- Node service runs `npm install && npm run dev -- --host 0.0.0.0`
- **Storefront not auto-built in Docker** — build manually inside container or locally
- Worker reuses `socialmediamanager-app:latest` image; runs Supervisor + Horizon

## Queue / Horizon

- **Driver**: Redis (production), `sync` (testing — `phpunit.xml`)
- **Named queues** (priority order): `facebook` > `high` > `default` > `low`
- **Jobs**: `SendAiReplyJob`, `AnalyzeProductImageJob`, `AnalyzeVariantImageJob`, `ProcessImageBatch`, `SyncCategoryAttributeTemplates`
- `SyncCategoryAttributeTemplates` syncs `BusinessCategory.extra_fields` JSON → `attribute_templates` in ALL tenant DBs (runs on BusinessCategory created/updated)
- Horizon dashboard at `/horizon` (local) or configured `HORIZON_PATH`

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

Minimal — 2 Laravel examples (`tests/Feature/ExampleTest.php`, `tests/Unit/ExampleTest.php`). No tenant-specific tests. Run with `composer test` (SQLite :memory:, `QUEUE_CONNECTION=sync`). `config:clear` required first (composer test handles it).

## Formatting

- `.editorconfig`: 4-space indent, LF endings (2-space `.yml`/`.yaml`)
- PHP: `./vendor/bin/pint` (Laravel Pint, no custom config)
- No Prettier, ESLint, or stylelint configs

## Gotchas

- `.env` uses MySQL+Redis; `.env.example` defaults to PostgreSQL + database-backed queue/cache/session
- `.env` has duplicate `QUEUE_CONNECTION=redis` — last one wins
- Root `package.json` Vite = Tailwind v4 (`@tailwindcss/vite`); storefront Vite = Tailwind v3 (PostCSS + `tailwindcss` + `autoprefixer`)
- `start.sh` orchestrates local dev (Docker + CLIP + dnsmasq + Apache proxy + Ngrok + storefront build)
- `setup-domain.sh` configures `smm.test` wildcard via dnsmasq + Apache reverse proxy to port 8000
- CLIP server has own `venv/` — managed via `clip-server/start.sh` or `start.sh`

## Planning Documents

`INVENTORY_REVIEW.txt` (bug list + architecture plan), `STOREFRONT_PLAN.txt`, `PRODUCT_CATEGORY_FIELDS_PLAN.txt`. Consult before modifying inventory or storefront — they capture known gaps and future work.
## Language / Communication Style
 
Always talk to me in **Banglish** (Romanized Bangla mixed with English) — not full formal English, not pure Bangla script. This means:
 
- Write Bangla words using English (Latin) alphabet, mixed naturally with English words/terms (especially technical terms, names, numbers).
- Keep the tone casual and conversational, like how people actually text/chat in Bangladesh.
- Example style: "Ami eta check kore dekhbo, kintu mone hocche eta thik ache. Tumi ki chao je ami eta directly fix kore dei?"
- Don't switch to pure Bangla script (unless I specifically ask for it).
- Don't reply in only formal English either — always keep the Banglish mix.
- Technical terms (code, file names, commands, error messages) should stay in English as-is — only the conversational/explanation parts need to be in Banglish.

