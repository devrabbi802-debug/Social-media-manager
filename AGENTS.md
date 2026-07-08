# AGENTS.md

## Project

SocialBoost AI — Laravel 13 multi-tenant SaaS for social media management. Bengali UI, MySQL in production, SQLite in-memory for tests. `stancl/tenancy` v3.10 (database-per-tenant, subdomain-based).

**Local Dev URL**: `http://smm.test/`

## Quick Commands

```bash
./start.sh              # PC on korle — Docker + DNS fix + Apache + Ngrok tunnel
composer setup          # install, key, migrate, npm install, build
composer dev             # artisan serve + queue:listen + pail + npm run dev (4 processes)
composer test            # config:clear + artisan test
php artisan test --filter=TestName
npx vite build

# Multi-Tenancy
php artisan tenants:migrate        # Migrate all tenant databases
php artisan tenants:seed           # Seed all tenant databases
php artisan tenants:run migrate    # Run migration for specific tenant
```

## Architecture

### Multi-Tenancy (Database-per-Tenant)

- **Landlord DB** (`socialboost`): `tenants`, `domains`, `admins`, `admin_user_permissions`, `ai_system_prompts`, `cache`, `jobs`, `sessions`
- **Tenant DB** (`{subdomain}_socialboost`): `users`, `sessions`, `password_reset_tokens`, `facebook_settings`, `ai_settings`, `conversations`, `messages`, `categories`, `attribute_templates`, `brands`, `products`, `product_attribute_values`, `product_variants`, `product_images`, `warehouses`, `stock_movements`, `inventory_alerts`
- **Users table does NOT exist in landlord DB** — never `User::count()` from central routes
- **Registration**: `Tenant::create()` → auto DB + migrate → user in tenant DB → redirect to `{subdomain}.smm.test`
- **Tenant custom attributes** in `data` JSON column — query: `where('data->status', 'active')`, NOT `where('status', 'active')`
- **Central domains** (not tenant): `127.0.0.1`, `localhost`, `smm.test`, `socialboost.com`, `www.socialboost.com`

### Auth System

- **Dual auth**: `web` guard (User, tenant DB) + `admin` guard (Admin, landlord DB)
- **Admin permissions**: `super_admin` bypasses all. Defined in `config/menu.php`, stored in `admin_user_permissions`
- **Admin routes loaded via** `then:` callback in `bootstrap/app.php`, NOT through `withRouting()`
- **`admin` middleware alias** registered in `bootstrap/app.php`

### AI Integration

- **Groq API** (Llama 3.3 70B) for auto-reply on Facebook Messenger
- **CLIP Server** (Local, Offline, Free) for image recognition and product matching
- **`AiSystemPrompt`** — landlord-level table (`ai_system_prompts`), global default prompt with `{company_name}` placeholder
- **`AiImagePrompt`** — landlord-level table (`ai_image_prompts`), image analysis prompt (editable from admin panel)
- **`AiSetting`** — tenant-level table (`ai_settings`), per-user `api_key` and optional `system_prompt`
- **`AiChatService`** (`app/Services/`) — Groq API wrapper, 15s timeout, handles 429 rate limiting, key rotation
- **`ClipService`** (`app/Services/`) — CLIP server wrapper for image embedding and matching
- **`SendAiReplyJob`** — queued on `facebook` queue, 3 tries, 45s backoff, `WithoutOverlapping` per tenant+sender. Sends typing indicators during AI processing
- **`ProcessImageBatch`** — Batch image analysis via CLIP server
- **`AnalyzeProductImageJob`** — Product image embedding via CLIP server, queued on `facebook` queue, stores embedding in `product_images.embedding` JSON column
- **Queue**: Redis, queue name `facebook`, Horizon supervisor (1-10 processes, 256MB memory)

### Facebook Integration

- **Webhook route** in `routes/web.php` (central, NOT tenant) — Facebook calls via ngrok tunnel URL
- **CSRF exemption**: `webhook/*` excluded in `bootstrap/app.php`
- **OAuth flow**: redirect → callback → auto-fetch page_id + page_access_token → save to `facebook_settings`
- **Verify token**: `socialboost_verify_token_2026` (hardcoded in `FacebookOAuthController`)
- **Multi-tenant webhook**: `FacebookWebhookController` iterates ALL tenants to find matching `page_id` (O(n), won't scale well)
- **Ngrok URL check**: `wget -qO- http://127.0.0.1:4040/api/tunnels`
- **Facebook App ID**: `703674879507719`, permissions: `pages_show_list`, `pages_messaging`, `pages_read_engagement`
- **Duplicate message handling**: Checks `facebook_mid` before saving incoming messages
- **Sender name fetching**: Uses Facebook Graph API to fetch sender's first_name + last_name

### Docker

```bash
docker compose up -d --build
docker compose down
docker compose logs -f
docker exec laravel-app php artisan <command>
```

- **7 services**: app, mysql (port 3307), node (port 5173), redis, phpmyadmin (port 8080), worker, clip-server (port 8089)
- **CLIP Server**: Python FastAPI server for image embedding and matching (local, offline, free)
- **Worker container**: Supervisor → `php artisan horizon` (not direct — see `docker/supervisord.conf`)
- **Entrypoint** (`docker-entrypoint.sh`): waits for MySQL → composer install → npm install → key:generate → migrate → serve
- `.env` uses `DB_HOST=mysql` (Docker service name), `.env.example` defaults to PostgreSQL

### Frontend

- Tailwind CSS v4 via `@tailwindcss/vite` plugin (no `tailwind.config.js` — config via CSS)
- **Public layouts**: Tailwind via CDN + Alpine.js (not Vite)
- **Dashboard/Admin**: Tailwind via CDN
- Bengali font: Hind Siliguri (Google Fonts), Instrument Sans (Vite fonts plugin)
- `app.js` is empty — no JS bundled via Vite yet

## Key Paths

- **Public views**: `resources/views/` (welcome, features, pricing, about, contact, auth)
- **Dashboard views**: `resources/views/dashboard/` — `index`, `integration`, `facebook-settings`, `facebook-select-page`, `ai-setup`, `products/` (CRUD+show), `categories/` (CRUD), `brands/` (CRUD), `warehouses/` (CRUD), `inventory/` (index+movements+alerts), `attribute-templates/` (CRUD)
- **Admin views**: `resources/views/admin/` (auth, dashboard, users CRUD, tenants CRUD, ai-system-prompt)
- **Layouts**: `resources/views/layouts/app.blade.php` (public), `resources/views/admin/layouts/app.blade.php` (admin)
- **Menu config**: `config/menu.php` — add admin sidebar menu groups here
- **Tenancy config**: `config/tenancy.php` — central domains, DB suffix, tenant model
- **Services config**: `config/services.php` — Facebook OAuth + Groq + Gemini model + CLIP server
- **Landlord migrations**: `database/migrations/`
- **Tenant migrations**: `database/migrations/tenant/`

## Missing Views (routes exist, views don't)

These views are referenced in `routes/web.php` and `routes/tenant.php` but **do not exist on disk**:
`dashboard.settings`, `dashboard.leads`, `dashboard.reports`, `dashboard.whatsapp`, `dashboard.facebook`

Visiting these routes throws `ViewNotFoundException`.

## Database

### Landlord DB Tables
1. `tenants` — id (string PK), data (JSON), timestamps
2. `domains` — id, domain, tenant_id (FK), timestamps
3. `admins` — id, name, email, password, role, timestamps
4. `admin_user_permissions` — id, admin_id (FK), menu_slug, permission, timestamps
5. `ai_system_prompts` — id, prompt_text, timestamps
6. `ai_image_prompts` — id, prompt_text, timestamps
7. `cache`, `jobs`, `sessions` — Standard Laravel tables

### Tenant DB Tables
1. `users` — id, name, email, phone, company, password, timestamps
2. `facebook_settings` — id, user_id (FK), app_id, app_secret, verify_token, page_id, page_access_token, ai_auto_reply_enabled, timestamps
3. `ai_settings` — id, user_id (FK), api_key, type, is_active, priority, timestamps
4. `conversations` — id, sender_id, sender_name, status, last_message_at, timestamps
5. `messages` — id, facebook_mid, conversation_id (FK), direction, type, content, image_path, image_analysis, timestamps
6. `categories` — id, parent_id (self-FK nullable), name, slug, description, image, sort_order, is_active, timestamps
7. `attribute_templates` — id, category_id (FK nullable when is_global), name, slug, type (text/number/select/boolean/date), options (JSON), is_required, is_global (boolean, default false), sort_order, timestamps
8. `brands` — id, name, slug, logo, is_active, timestamps
9. `products` — id, category_id (FK), brand_id (FK nullable), name, slug, sku, description, base_price, discount_price, stock_quantity, unit, barcode, status, is_featured, meta_title, meta_description, sort_order, timestamps
10. `product_attribute_values` — id, product_id (FK), attribute_template_id (FK), value, timestamps (unique on product_id + attribute_template_id)
11. `product_variants` — id, product_id (FK), sku, name, price, stock_quantity, attributes (JSON), barcode, is_active, timestamps
12. `product_images` — id, product_id (FK), image_path, alt_text, sort_order, image_analysis (JSON), embedding (JSON), timestamps
13. `variant_images` — id, variant_id (FK), image_path, alt_text, sort_order, image_analysis (JSON), embedding (JSON), timestamps
14. `warehouses` — id, name, address, phone, is_active, timestamps
15. `stock_movements` — id, product_id (FK), variant_id (FK nullable), warehouse_id (FK), type (in/out/adjustment), quantity, reference, notes, created_by (FK nullable), timestamps
16. `inventory_alerts` — id, product_id (FK unique), threshold, is_active, timestamps
17. `attribute_options` — id, attribute_template_id (FK), value, slug, sort_order, is_active, timestamps (unique on attribute_template_id + slug)
18. `stock_transfers` — id, product_id (FK), variant_id (FK nullable), from_warehouse_id (FK), to_warehouse_id (FK), quantity, status (pending/completed/cancelled), notes, created_by (FK nullable), timestamps
19. `password_reset_tokens`, `sessions` — Standard Laravel tables

### Seeder
- `AdminSeeder` creates `admin@socialboost.com` / `Admin@123456`, role `super_admin` (idempotent via `updateOrCreate`)

## Testing

- PHPUnit 12, SQLite in-memory, sync queue, array cache/session/mail
- Pulse/Telescope/Nightwatch disabled in tests
- `composer test` clears config cache first (important for env overrides)
- No real tests yet — only placeholder `ExampleTest`

## Gotchas

- **`app.js` is empty** — no JS bundled via Vite yet
- **`.env.example` defaults to PostgreSQL** — actual `.env` uses MySQL
- **`APP_LOCALE=en`** — Bengali is hardcoded in Blade templates, not via locale config
- **`.npmrc`**: `ignore-scripts=true` — postinstall scripts skipped
- **`storage/framework/views/`** excluded from Vite watch but NOT gitignored
- **No `pint.json`** — Laravel Pint uses defaults
- **No CI workflows** configured
- **No `tailwind.config.js`** — Tailwind v4 uses CSS-based config
- **User model** uses Laravel 11+ `#[Fillable]`/`#[Hidden]` PHP attributes. **Admin model** uses traditional `$fillable`/`$hidden` arrays — style differs between the two
- **`Admin::getAllPermissions()`** references `\App\Services\Menu::class` which **does not exist** — will error if called by non-super_admin
- **Registration validation** does NOT use `unique:users,email` (users are per-tenant)
- **Facebook OAuth**: HTTP not allowed — use ngrok HTTPS URL
- **Facebook test users**: Dev mode only testers/admins trigger webhooks
- **Horizon**: runs inside Docker worker container via Supervisor, not directly
- **Redis**: queue + cache driver in `.env` (`QUEUE_CONNECTION=redis`, `CACHE_STORE=redis`)

## Inventory System Improvements (Phase 1-4 Complete)

### Bug Fixes Applied
- **Stock operations** now wrapped in `DB::transaction()` — prevents race conditions
- **Variant stock recalculation** — `recalculateStock()` called after every variant stock change
- **Attribute stale values** — existing attributes deleted before re-saving on product edit
- **Low stock alert** — now checks variant stock if product has variants
- **Category dropdown** — supports subcategories with hierarchical indentation
- **Product status** — removed auto-override from model boot hook (user's choice respected)
- **Variant modal URL** — fixed string concatenation bug in show.blade.php

### New Tables
- **`attribute_options`** — standardized values per attribute (for future variant matrix)
- **`stock_transfers`** — atomic stock movement between warehouses

### New Controllers
- **`StockTransferController`** — full CRUD + complete/cancel workflow with DB transactions

### Key Architecture Decisions
- **`Product::recalculateStock()`** — public method on model (not private on controller)
- **Stock In/Out/Adjust** — all wrapped in `DB::transaction()` for atomicity
- **`StockTransfer`** — creates 2 StockMovement records (out + in) atomically
- **`InventoryAlert::isLowStock()`** — variant-aware (checks variant stock if variants exist)

## Shopify-Style Product Creation System (Phase 5 Complete)

### How It Works Now
```
Step 1: Title + Description + Category
Step 2: Price + Status
Step 3: OPTIONS define koro (Color → Red, Blue, Green | Size → S, M, L)
Step 4: AUTO-GENERATE variant matrix (3×3 = 9 variants)
Step 5: Per-variant: SKU (auto), Price, Stock, Barcode
```

### Database Changes
- **`attribute_templates.is_variant_option`** — boolean, marks if template is a variant option (Color/Size) vs product-level attribute (Material/Weight)
- When creating product with options, system creates `AttributeTemplate` records with `type = 'option'` and `is_variant_option = true`
- `options` JSON column stores values: `["Red", "Blue", "Green"]`

### Variant Matrix Generation
- JavaScript `getCombinations()` — recursive cartesian product of all option values
- Auto SKU format: `{PRODUCT-SKU}-{OPTION1}-{OPTION2}` (e.g., `TSHIRT-RED-M`)
- Matrix renders as editable table: SKU, Price, Stock, Barcode per row

### Key Files
```
app/Http/Controllers/Dashboard/ProductController.php — store() handles options + variants
app/Http/Controllers/Dashboard/StockTransferController.php — stock between warehouses
app/Models/AttributeTemplate.php — is_variant_option field
app/Models/AttributeOption.php — standardized option values
app/Models/StockTransfer.php — warehouse transfer model
resources/views/dashboard/products/create.blade.php — Shopify-style wizard
resources/views/dashboard/products/edit.blade.php — shows existing + new options
resources/views/dashboard/inventory/transfers.blade.php — stock transfer UI
```

### Option vs Attribute Distinction
| Type | Purpose | Example | Where Used |
|------|---------|---------|------------|
| Variant Option | Generates variants | Color, Size | Product create → Options section |
| Product Attribute | Extra info | Material, Weight | Product create → Attributes section |

### Variant Attributes Storage
- Variant attributes stored as JSON: `{"Color": "Red", "Size": "M"}`
- Product-level attributes stored in `product_attribute_values` table
- Both coexist — variant attrs are for variant identification, product attrs are for display

# Agent Instructions

Always reply in Banglish (Bengali written in English letters).
No matter what language the user writes in, always respond in Banglish.
Example: "Ei function ta fix korte hobe, karon ekhane error ache."

Always follow best practices for coding architecture (SOLID, DRY, KISS, YAGNI).
Follow Laravel conventions and the conventions of the frameworks/libraries used.
