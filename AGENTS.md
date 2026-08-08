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

`config:clear` required before `artisan test` (composer test handles it). Root `.npmrc` sets `ignore-scripts=true` — affects storefront npm install too. `composer setup` only copies `.env.example`→`.env` if `.env` missing.

**Storefront has own npm**: `cd resources/storefront && npm install && npm run build` (output → `public/storefront/`, own Vite 5 + React 18). Not covered by `composer setup`.

## Architecture

| Layer | Location | Stack |
|---|---|---|
| Backend | `app/`, `routes/` | PHP 8.3+, Laravel 13, stancl/tenancy v3, Horizon (Redis), MySQL |
| Admin UI | Blade + Alpine.js | Tailwind v4 (`@tailwindcss/vite`), root Vite 8 |
| Storefront SPA | `resources/storefront/` | React 18, React Router 6, Vite 5, Tailwind v3 (own PostCSS), Axios |
| CLIP Server | `clip-server/` | Python FastAPI, OpenAI CLIP (offline) |

**APP_NAME**: `Get ERP Store` (set in `.env`).

## Routes (Load Order)

1. `routes/web.php` — landing pages, onboarding, webhooks (CSRF-exempt via `bootstrap/app.php:30-33`)
2. `routes/api.php` — storefront SPA API (tenant-scoped). Groups: `/api/storefront/*` (public), `/api/auth/*` (public + `auth:sanctum` protected), `/api/checkout/*` (public), `/api/customer/*` (`auth:sanctum`), `/api/editor/*` (public CRUD + upload), `/api/themes/*`
3. `routes/console.php` — Artisan commands
4. `routes/admin.php` — central admin `/rootadmin/*` (guard `admin`, landlord DB). Loaded via `bootstrap/app.php` `then:` callback.
5. `routes/tenant.php` — per-tenant dashboard, inventory, Facebook OAuth, **storefront catch-all LAST**. Loaded by `TenancyServiceProvider::mapRoutes()` on `booted`.

`tenant.php` defines named routes (`login`, `register`, `logout`). Central routes deliberately leave these unnamed — `withRouting()` loads `web.php` after `tenant.php`; a name conflict would overwrite.

## Tenancy

- **DB naming**: prefix `''` + tenant_id + suffix `'_socialboost'` (`config/tenancy.php:56-57`)
- **Central domains**: `127.0.0.1`, `localhost`, `smm.test`, `socialboost.com`, `www.socialboost.com`
- **Tenant admin prefix**: `ADMIN_PANEL_PREFIX` env var (default `ax7k9m`)
- **Central admin prefix**: hardcoded `/rootadmin`
- **`public` disk NOT tenant-aware** — use `tenant_asset()` not `asset()` (`config/tenancy.php:141` `asset_helper_tenancy => false`)
- **`locale` middleware** on all tenant routes (sets locale from `user.locale` column)
- **`central` middleware** (`PreventAccessFromNonCentralDomains`) on central-only routes
- **`TenantCouldNotBeIdentifiedOnDomainException` → 404** (`bootstrap/app.php:40-42`)
- **Tenant DB auto-created** on creation via `CreateDatabase` + `MigrateDatabase` job pipeline

## Storefront SPA

- Own `node_modules/`, own `vite.config.js` (Vite 5, no laravel-vite-plugin)
- **Build**: `cd resources/storefront && npm run build` → `public/storefront/` (manifest enabled)
- **Watch**: `npm run watch` — auto-rebuild on change (no HMR)
- **Dev proxy**: `vite.config.js` proxies `/api` → `http://localhost:8000`
- **Auth (storefront)**: Laravel Sanctum token-based using **`customers`** table (Customer model). Token stored in `localStorage` as `auth_token`. Auto-attached via Axios interceptor. 401 response clears token and redirects to `/auth`.
- **Auth (admin dashboard)**: Session-based using **`users`** table (User model, `web` guard) — separate from storefront auth.
- **Sanctum multi-model**: `personal_access_tokens.tokenable_type` distinguishes Customer vs User tokens. `auth:sanctum` checks session first (`sanctum.guard` = `['web']`), then falls back to Bearer token. Active admin session can shadow Customer token — be aware when debugging storefront auth.
- **Themes**: lazy-loaded via `resources/storefront/src/themes/index.js`. 2 registered: `clothing-fashion` (default) and `classic` — both exist at `resources/storefront/src/themes/{slug}/`.
- **Cart**: client-side React Context + `localStorage` persistence (key `storefront_cart`). No backend cart API.
- **Editor mode**: `?editor=true` URL param toggles `EditableSection` overlays for admin theme editing.
- **`sections_data`** JSON column on `StorefrontSettings` stores editor state (banners, categories, section_titles, etc.)
- **Guest checkout**: `POST /api/checkout/place` works without auth. Phone required (validated backend + frontend). Guest auto-gets `Customer` record (type=`guest`) created/found by phone. Shipping address saved to `customer_addresses` table. Track order by phone + order number.
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

- `docker-entrypoint.sh`: waits for MySQL → `composer install --no-dev` → `npm install` (root only) → `key:generate` → `migrate` → `octane:start --server=swoole --host=0.0.0.0 --port=8000` (install steps are skipped if `vendor/`/`node_modules/` already exist)
- Node service runs `npm install && npm run dev -- --host 0.0.0.0`
- **Storefront not auto-built in Docker** — build manually inside container or locally
- Worker reuses `socialmediamanager-app:latest` image; runs Supervisor + Horizon

## Queue / Horizon

- **Driver**: Redis (production), `sync` (testing — `phpunit.xml`)
- **Named queues** (priority order): `facebook` > `high` > `default` > `low`
- **Jobs**: `SendAiReplyJob`, `AnalyzeProductImageJob`, `AnalyzeVariantImageJob`, `ProcessImageBatch`, `SyncCategoryAttributeTemplates`, `GenerateTextEmbeddingJob`, `GenerateVariantTextEmbeddingJob`
- `SyncCategoryAttributeTemplates` syncs `BusinessCategory.extra_fields` JSON → `attribute_templates` in ALL tenant DBs (runs on BusinessCategory created/updated)
- Horizon dashboard at `/horizon` (local) or configured `HORIZON_PATH`

## Key Services (`app/Services/`)

| Service | Purpose |
|---|---|
| `AiChatService` | AI reply generation (Groq/Cerebras/Gemini, per-tenant config) |
| `ClipService` | CLIP image matching (posts to `clip-server:8089`) |
| `TextSearchService` | Text embedding generation (posts to `clip-server:8089/text-embed`) |
| `ZernioService` | Zernio social media API v1 |
| `AudioTranscriptionService` | Audio→text for voice messages |
| `ChatOrderService` | Order creation from chat conversations |
| `ChatSelectionService` | Chat routing/mode selection (manual/AI) |
| `ProductContextService` | Manages product context during conversations |
| `AiTools/` | AI tool registry + executor (searched by `ToolRegistry`/`ToolExecutor`) |

## POS System (`app/Http/Controllers/Dashboard/Pos*Controller`, `resources/views/tenant/pos/`)

Separate `pos_*` tables (NOT the e-commerce `orders` table). Tenant-scoped under the admin prefix at `/pos/*`. Scope: terminal (`pos.index`, Alpine.js cart + product grid from `GET /pos/products` JSON), register sessions (`pos.sessions.*`, cash in/out + X-report on close), sales (`pos.sales.*`, receipt print + partial/full refund with stock restore), reports (`pos.reports`, cost-based profit), settings (`pos.settings`).

- **Stock deducted** on checkout via `StockMovement` (type `out`, requires `warehouse_id` — POS falls back to `pos_settings.default_warehouse_id` then first active warehouse). Refunds restore stock (`type` `in`).
- Checkout/hold post `items_json`/`payments_json` JSON strings (not array inputs). Split payments → `pos_payments` rows; underpaid → `payment_status=partial` (credit sale).
- Register session optional: checkout works without one but terminal shows an "open register" banner.
- `cost_price` added to `products` + `product_variants` (nullable) for profit reporting.

## Purchase System (`app/Http/Controllers/Dashboard/Purchase*Controller.php`, `resources/views/tenant/purchase/`, `Dashboard/PurchaseController.php`)

Supplier + procurement subsystem (tenant-scoped) at `{adminPrefix}/purchase/*`, group-gated by `purchase_dashboard,list`. Flow: `suppliers` → purchase `orders` (PO) → `receipts` (GRN, stock in via `StockMovement`) → `invoices` (bills, `pay` action) → `payments` (supplier payments incl. advances on PO) → `returns` (stock restore + `postPurchaseReturn`). `direct` creates PO+GRN+(optional invoice) in one transaction.

- **Tables**: `suppliers`, `supplier_payments`, `purchase_orders`, `purchase_order_items`, `purchase_receipts`, `purchase_receipt_items`, `purchase_invoices`, `purchase_invoice_items`, `purchase_returns`, `purchase_return_items`, `purchase_settings`. Payment methods on `SupplierPayment`.
- **Accounting-integrated** (gated by `PurchaseSetting::current()->auto_post_purchases`, NOT `accounting_settings`): `postPurchaseInvoice()`, `postSupplierPayment()`, `postSupplierAdvance()`, `postPurchaseReturn()` on `AccountingService`. Invoice cancel/delete → `reverse()` the `purchase_invoice` reference entry (find via `JournalEntry::ofReference('purchase_invoice', $id)`).
- Permissions (`config/tenant-permissions.php` `purchase` group): `purchase_dashboard`, `suppliers`, `purchase_orders`, `purchase_receipts`, `purchase_invoices` (+`pay`), `supplier_payments`, `purchase_returns`, `purchase_reports`, `purchase_settings`.

## Inventory (`app/Http/Controllers/Dashboard/`)

Products, variants, categories, brands, attribute templates, warehouses, stock movements, transfers. **No FormRequest classes** — validation inline in controllers.

**Known bugs** (see `INVENTORY_REVIEW.txt`): variant stock not synced to parent, stock ops lack DB transactions, stale attribute values on edit, low-stock alert ignores variants, subcategory assignment broken on create.

## Tenant RBAC (Roles & Permissions)

Custom role-based access control for the **tenant admin panel** — fully separate from the central `config/menu.php` `Admin` RBAC. Do NOT use spatie here.

- **Registry**: `config/tenant-permissions.php` — `groups` (modules) each with `items` (sub-menu `slug` + `permissions`). The permission matrix in the UI, the sidebar filter, and route middleware all reference these `"slug.action"` strings.
- **Table**: `tenant_roles` (tenant DB) — `permissions` JSON column holds `["module.action", ...]`. `users.role_id` FK (`nullOnDelete`) added by migration `tenant/2026_08_06_000001_create_tenant_roles_table.php`.
- **Models**: `App\Models\Role` (`permissionList()`, `hasPermission()`); `App\Models\User` — `role()`, `isSuperAdmin()` (`role_id === null` ⇒ owner/full access ⇒ bypasses all checks), `hasPermission($module,$action)`, `permissionList()`.
- **Middleware**: `EnsureTenantPermission` registered as alias `permission` in `bootstrap/app.php`. Usage: `Route::middleware('permission:products,edit')`. 403 on deny.
- **Controllers/Views**: `app/Http/Controllers/Dashboard/{RoleController,UserController}.php`; views `resources/views/tenant/{roles,users,partials/_permission-matrix}.blade.php` (Alpine permission matrix). Routes: `routes/tenant.php` `{adminPrefix}/roles/*` and `/users/*` gated by `permission:user_management,*`.
- **Sidebar**: `layouts/tenant.blade.php` — each menu item carries a `permission` slug; groups hidden if no accessible child. Dashboard top-link is **not** permission-gated.
- **Defaults**: first visit to roles page auto-creates `Manager` + `Sales Agent` roles if the `roles` table is empty (`RoleController::ensureDefaults()`).
- **Dashboard is ALWAYS accessible** to any logged-in tenant user (no `permission:dashboard,list` middleware on the `/dashboard` route) so login never 403s. Most other modules enforce their module `,list` + per-action (`create`/`edit`/`delete`/`view`/`export`/`refund`/`close`/`hold`).
- **Route scope**: enforcement is sub-menu granular — e.g. `products,create` vs `products,delete`, `pos_sales,refund`, `pos_sessions,close`, `stock_transfers,create`.
- **NOTE (Octane)**: tenant routes are loaded per-request by `TenancyServiceProvider::mapRoutes()`; Octane (Swoole) keeps workers in memory, so after editing `routes/tenant.php` or tenant views you MUST run `docker exec laravel-app php artisan octane:reload` — otherwise you'll get `RouteNotFoundException`/stale routes.

## Tests

Minimal — 3 files: `tests/Feature/ExampleTest.php`, `tests/Unit/ExampleTest.php`, `tests/Unit/AiToolCallingTest.php`. `AiToolCallingTest` covers `app/Services/AiTools/` (ToolRegistry/ToolExecutor) without network. No tenant-specific tests. Run with `composer test` (SQLite :memory:, `QUEUE_CONNECTION=sync`). `config:clear` required first (composer test handles it).

## Accounting System

Double-entry ledger (tenant-scoped) with a non-accountant-friendly UI. Routes under `{adminPrefix}/accounting/*`, views `resources/views/tenant/accounting/`.

- **Tables**: `chart_of_accounts`, `journal_entries` (voucher header, `status` = `posted`/`reversed`), `journal_entry_lines`, `accounting_settings` (singleton id=1, auto-post toggles + payment→account map).
- **Engine**: `app/Services/AccountingService.php` — `post()` validates debit=credit, `reverse()` creates a **reversing entry** (original stays `posted`; they offset in the ledger — do NOT mark the original reversed in code). `ensureChartOfAccounts()` seeds default COA (codes 1010+ assets, 20xx liabilities, 30xx equity, 4010 sales, 50xx expenses) on first visit. Reports: `trialBalance()`, `incomeStatement()`, `balanceSheet()`, `ledger()`, `netProfit()` (fiscal year = July start by default, configurable).
- **Auto-posting hooks** (respect `accounting_settings.post_pos_*` / `post_storefront_orders`): `PosController::checkout` → `postPosSale()`, `PosSaleController::refund` → `postPosRefund()` (full = reversing entry, partial = sales-return), `CheckoutController::placeOrder` → `postOrder()` (receivable until paid), `OrderController::receivePayment` (AR→cash) + `reverseOrderEntries()` on cancel/refund. **Purchase hooks** gated by `PurchaseSetting::auto_post_purchases` instead (see Purchase System).
- **Opening balances**: entered on the Accounting Settings page → `syncOpeningBalances()` deletes prior `opening` reference entries then posts a balanced entry, net parked in `3100 Opening Balance Equity`.
- **Payments**: `paymentAccount($method)` maps cash/bkash/nagad/rocket/upay/card to COA via settings `payment_account_map` (defaults: 1010 cash, 1020 bank, 1030 mobile wallet).
- **Permissions**: `accounting` group in `config/tenant-permissions.php` (dashboard, money, chart_of_accounts, journal_entries incl. `reverse`, reports, settings). Manager default role has view-level access.
- Balance columns are `decimal(14,2)`; all report math uses `round(...,2)`.

## Formatting

- `.editorconfig`: 4-space indent, LF endings (2-space `.yml`/`.yaml`, 4-space `docker-compose.*.yml`)
- PHP: `./vendor/bin/pint` (Laravel Pint, no custom config)
- No Prettier, ESLint, or stylelint configs

## Gotchas

- `.env` uses MySQL+Redis; `.env.example` defaults to PostgreSQL + database-backed queue/cache/session
- `.env` has duplicate `QUEUE_CONNECTION=redis` — last one wins
- `.env` `CACHE_STORE=redis` vs `.env.example` `CACHE_STORE=database`
- `CLIP_SERVER_URL` in `.env` = `http://clip-server:8089` (Docker hostname); `.env.example` uses `localhost:8089` (local dev)
- `ADMIN_PANEL_PREFIX` in `.env` = `supermaster` (deviates from default `ax7k9m` in `config/app.php`)
- Root `package.json` Vite = Tailwind v4 (`@tailwindcss/vite`); storefront Vite = Tailwind v3 (PostCSS + `tailwindcss` + `autoprefixer`)
- `start.sh` orchestrates local dev (Docker + CLIP + dnsmasq + Apache proxy + Ngrok + storefront build)
- **Public media URLs come from `MEDIA_URL`, NOT `APP_URL`** (`config/services.php:35`, set to current Ngrok URL in `.env`). `ProductImage`/`VariantImage` accessors and `AiTools/ToolExecutor` use it to build absolute URLs for FB/WhatsApp media. When the Ngrok tunnel URL rotates, update `MEDIA_URL` (mirrored in `.ngrok-url` at repo root) or sent media breaks.
- `setup-domain.sh` configures `smm.test` wildcard via dnsmasq + Apache reverse proxy to port 8000
- CLIP server has own `venv/` — managed via `clip-server/start.sh` or `start.sh`

## Planning Documents

`INVENTORY_REVIEW.txt` (bug list + architecture plan), `STOREFRONT_PLAN.txt`, `PRODUCT_CATEGORY_FIELDS_PLAN.txt`. Consult before modifying inventory or storefront — they capture known gaps and future work.

## User Documentation

`docs/ACCOUNTING_USER_GUIDE.md` (EN) + `docs/ACCOUNTING_USER_GUIDE_BN.md` (Bangla) — complete end-user guide for the accounting module (setup, journal, reports, reversal, FAQ). Update these when accounting behavior/views change.


## Language / Communication Style
 
Always talk to me in **Banglish** (Romanized Bangla mixed with English) — not full formal English, not pure Bangla script. This means:
 
- Write Bangla words using English (Latin) alphabet, mixed naturally with English words/terms (especially technical terms, names, numbers).
- Keep the tone casual and conversational, like how people actually text/chat in Bangladesh.
- Example style: "Ami eta check kore dekhbo, kintu mone hocche eta thik ache. Tumi ki chao je ami eta directly fix kore dei?"
- Don't switch to pure Bangla script (unless I specifically ask for it).
- Don't reply in only formal English either — always keep the Banglish mix.
- Technical terms (code, file names, commands, error messages) should stay in English as-is — only the conversational/explanation parts need to be in Banglish.

