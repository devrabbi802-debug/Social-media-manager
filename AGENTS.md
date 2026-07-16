# AGENTS.md

## Project

SocialBoost AI — Laravel 13 multi-tenant SaaS for social media management. Multi-language UI (Bengali/English), MySQL production, SQLite in-memory tests. `stancl/tenancy` v3.10 (database-per-tenant, subdomain-based).

**Local Dev URL**: `http://smm.test/`

## Quick Commands

```bash
./start.sh              # PC on korle — Docker + DNS fix + Apache + Ngrok tunnel
composer setup          # install, key, migrate, npm install, build
composer dev             # artisan serve + queue:listen --tries=1 + pail + npm run dev (4 processes via concurrently)
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

- **Landlord DB** (`socialboost`): `tenants`, `domains`, `admins`, `admin_user_permissions`, `ai_system_prompts`, `business_categories`, `cache`, `jobs`, `sessions`
- **Tenant DB** (`{subdomain}_socialboost`): `users`, `facebook_settings`, `ai_settings`, `conversations`, `messages`, `categories`, `attribute_templates`, `brands`, `products`, `product_attribute_values`, `product_variants`, `product_images`, `warehouses`, `stock_movements`, `inventory_alerts`, `stock_transfers`, `attribute_options`, `variant_images`, `business_settings`
- **Users table does NOT exist in landlord DB** — never `User::count()` from central routes
- **Registration**: `GET /register` redirects to `/onboarding` — 8-step wizard (account, business info, tone, pricing, delivery, FAQ, escalation, logo) → `Tenant::create()` + `BusinessSetting::create()` → auto-login → redirect to `{subdomain}.smm.test/dashboard`
- **Onboarding**: Alpine.js multi-step form with step validation. Searchable category select with custom category creation. Real-time subdomain availability check via AJAX. Stores all business config in `business_settings` table (tenant DB). AI system prompt auto-generated from `BusinessSetting::generateSystemPrompt()`
- **Tenant custom attributes** in `data` JSON column — query: `where('data->status', 'active')`, NOT `where('status', 'active')`
- **Central domains** (not tenant): `127.0.0.1`, `localhost`, `smm.test`, `socialboost.com`, `www.socialboost.com`
- **Tenant migrations**: `database/migrations/tenant/` — run via `php artisan tenants:migrate`
- **Tenant routes**: Loaded by `TenancyServiceProvider::mapRoutes()` in `app/booted` callback, NOT via `withRouting()` — tenant routes coexist with central routes in one route collection

### Route Priority & Domain Blocking

- **Central routes** (`routes/web.php`): Wrapped in `PreventAccessFromNonCentralDomains` middleware — blocks non-existent subdomains (e.g. `rabbi.smm.test` → 404), allows central domains AND valid tenant domains
- **Webhook routes** (`/webhook/facebook`, `/webhook/zernio`): Outside middleware group — accessible from any domain (Facebook/Zernio calls via tunnel)
- **Tenant routes** (`routes/tenant.php`): Use `InitializeTenancyByDomain` + `PreventAccessFromCentralDomains` — blocks central domains from accessing tenant routes
- **`TenantCouldNotBeIdentifiedOnDomainException`** → caught in `bootstrap/app.php`, renders 404
- **Custom 404 page**: `resources/views/errors/404.blade.php` — Bengali text, gradient styling
- **Gotcha**: Central routes are registered FIRST (via `withRouting`), tenant routes later (via `TenancyServiceProvider::boot()`). For paths that exist in both files (e.g. `/dashboard`), the central `web.php` version wins. Tenant routes only work because `InitializeTenancyByDomain` middleware switches DB context.

### Auth System

- **Dual auth**: `web` guard (User, tenant DB) + `admin` guard (Admin, landlord DB)
- **Admin permissions**: `super_admin` bypasses all. Defined in `config/menu.php`, stored in `admin_user_permissions`
- **Admin routes loaded via** `then:` callback in `bootstrap/app.php`, NOT through `withRouting()`
- **Middleware aliases** in `bootstrap/app.php`: `admin` → `AdminMiddleware`, `locale` → `SetLocale`, `central` → `PreventAccessFromNonCentralDomains`
- **User model** uses Laravel 11+ `#[Fillable]`/`#[Hidden]` PHP attributes. **Admin model** uses traditional `$fillable`/`$hidden` arrays — style differs between the two
- **`Admin::getAllPermissions()`** references `\App\Services\Menu::class` which **does not exist** — will error if called by non-super_admin

### Onboarding System (8-Step Wizard)

- **Route**: `GET /onboarding` → `OnboardingController@index`, `POST /onboarding` → `OnboardingController@store`
- **Subdomain check**: `POST /check-subdomain` → `SubdomainController@check` — real-time AJAX availability check (500ms debounce)
- **Navbar button**: Top-right "Let's Start" button (visible when logged out) → redirects to `/onboarding`
- **Step validation**: Alpine.js client-side validation blocks "Next" button if required fields empty
- **Subdomain auto-clean**: `@input` event auto-converts to lowercase, removes spaces/special chars
- **Category select**: Searchable dropdown with custom category creation (user types name → "➕ যোগ করুন" → creates in `business_categories` table on submit)
- **JSON hidden inputs**: `accepted_payment_methods` and `delivery_areas` use `JSON.stringify()` in hidden inputs — controllers decode with `json_decode()` before validation

**8 Steps:**
1. **Account**: name, email, phone, subdomain (with availability check), password
2. **Business Info**: business name, category (searchable + custom), sub-category, AI persona name, business hours, off-hours message, description
3. **Tone & Communication**: formality level (formal/casual), emoji usage (never/sometimes/often), language style (shuddho/anjonio/banglish), greeting style
4. **Pricing Policy**: price negotiation toggle + limit %, bulk discount rule, current promo text
5. **Delivery & Payment**: delivery areas (dynamic list with name+price, default: Inside Dhaka/Outside Dhaka), time, partner, COD toggle, payment methods (dynamic list with name+details), advance payment toggle + %, advance for outside Dhaka toggle, refund policy, exchange policy, order process message
6. **Custom FAQ**: dynamic Q&A pairs (add/remove)
7. **Escalation Rules**: escalation keywords (comma-separated), human contact info
8. **Logo**: drag-drop or file upload (optional)

**Controller flow** (`OnboardingController@store`):
1. Decode JSON strings (`accepted_payment_methods`, `delivery_areas`) from hidden inputs
2. Validate all fields
3. If `custom_category_name` provided (no `category_id`): `BusinessCategory::firstOrCreate()` with slug, icon 📦, sort_order 99
4. `Tenant::create()` + `Domain::create()`
5. `$tenant->run()`: create `User` + `BusinessSetting` in tenant DB
6. Logo upload to `storage/logos/`
7. FAQ filtered (empty Q/A removed)
8. Auto-login + redirect to tenant dashboard

### Localization (Multi-Language)

- **Languages**: Bengali (`bn`) — default, English (`en`)
- **Storage**: User's `locale` column in tenant DB `users` table (persistent across sessions)
- **Middleware**: `SetLocale` (`app/Http/Middleware/SetLocale.php`) — reads `$request->user()->locale`, sets `app()->setLocale()`. Registered as `locale` alias in `bootstrap/app.php`
- **Route**: `POST /language/switch` (`LanguageController@switch`) — updates user locale, redirects back
- **Switcher UI**: Topbar — dedicated language dropdown (globe icon + "বাংলা/English") + profile dropdown toggle
- **Translation files**: `lang/bn/` and `lang/en/` (18 files each)
  - `sidebar.php`, `common.php`, `tenant.php`, `nav.php`, `dashboard.php`, `settings.php`, `integration.php`, `conversations.php`, `facebook.php`, `ai.php`, `image_match.php`, `products.php`, `categories.php`, `brands.php`, `warehouses.php`, `inventory.php`, `attributes.php`, `auth.php`
- **Usage in Blade**: `@lang('file.key')` or `__('file.key')`. For dynamic text: `__('file.key', ['var' => $value])`
- **Default locale**: `bn` (Bengali) — new users get Bengali by default, existing unaffected
- **`<html lang>`** attribute set dynamically: `<html lang="{{ app()->getLocale() }}">`
- **Route registration**: `locale` middleware added to tenant route group in `routes/tenant.php`

### AI Integration

- **3-tier fallback chain**: **Groq → Cerebras → Gemini**
- **Groq API** (Llama 3.3 70B) — Primary, OpenAI-compatible
- **Cerebras API** (gpt-oss-120b) — Secondary, free tier, OpenAI-compatible
- **Gemini API** (Flash Lite) — Tertiary fallback
- **CLIP Server** (Local, Offline, Free) for image recognition and product matching (`http://localhost:8089`)
- **`AiSystemPrompt`** — landlord-level table, global default prompt with `{company_name}` placeholder
- **`AiSetting`** — tenant-level table, per-user `api_key` with `type` field (`message`=Groq, `cerebras`=Cerebras, `image`=Gemini)
- **`AiChatService`** (`app/Services/`) — Multi-provider wrapper, 15-30s timeout, handles 429, key rotation per provider
- **`ClipService`** (`app/Services/`) — CLIP server wrapper for image embedding and matching
- **`SendAiReplyJob`** — queued on `facebook` queue, 5 tries, 15s backoff, 180s timeout. Fallback: all Groq keys → all Cerebras keys → all Gemini keys
- **Queue**: Redis, queue name `facebook`, Horizon supervisor (1-10 processes, 256MB memory)
- **Key rotation**: Within each provider, iterates all active keys by priority before falling back to next provider
- **AI system prompt** includes: business info, communication style, pricing, delivery areas (structured), payment methods (structured), refund/exchange policies, order process message
- **Order process message**: Customizable template stored in `business_settings.order_process_message` — AI sends this when customer wants to order

### Facebook Integration

- **Dual connection modes**: Facebook App (direct) OR Zernio (third-party API)
- **Webhook route** in `routes/web.php` (central, NOT tenant) — Facebook calls via ngrok tunnel URL
- **CSRF exemption**: `webhook/*` and `facebook/zernio/test-webhook` excluded in `bootstrap/app.php`
- **Verify token**: `socialboost_verify_token_2026` (hardcoded in `FacebookOAuthController`)
- **Multi-tenant webhook**: `FacebookWebhookController` iterates ALL tenants to find matching `page_id` or `zernio_account_id` (O(n), won't scale well)
- **Ngrok URL check**: `wget -qO- http://127.0.0.1:4040/api/tunnels`
- **Facebook App ID**: `703674879507719`, permissions: `pages_show_list`, `pages_messaging`, `pages_read_engagement`
- **Duplicate message handling**: Checks `facebook_mid` before saving incoming messages

### Zernio Integration

- **Purpose**: Connect Facebook Pages without creating a Facebook App — per-tenant Zernio accounts
- **API Base**: `https://zernio.com/api/v1` (configured in `config/services.php` → `zernio.base_url`)
- **Connection type**: `facebook_settings.connection_type` — enum `facebook_app` (direct) or `zernio`
- **Flow**: Save API key → create profile → OAuth Facebook → select page → save to `facebook_settings`
- **Key files**: `app/Services/ZernioService.php`, `app/Http/Controllers/ZernioOAuthController.php`
- **Webhook**: `POST /webhook/zernio` — handles `message.received`, `conversation.started`, `account.connected`, `account.disconnected`
- **Fallback**: If Zernio connection, `SendAiReplyJob` uses Zernio API; otherwise Facebook Graph API directly
- **Gotcha**: Zernio profile names must be unique — controller appends `time()` to avoid conflicts
- **Gotcha**: `tempToken` from OAuth callback is required for page selection — stored in session

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
- **Tenant/Admin**: Tailwind via CDN (dashboard sidebar layout)
- Bengali font: Hind Siliguri (Google Fonts), Instrument Sans (Vite fonts plugin)
- `app.js` is empty — no JS bundled via Vite yet

## Key Paths

- **Public views**: `resources/views/` (welcome, features, pricing, about, contact, auth)
- **Onboarding views**: `resources/views/onboarding/` — multi-step wizard (Alpine.js)
- **Tenant views**: `resources/views/tenant/` — `index`, `integration`, `facebook-settings`, `facebook-select-page`, `ai-setup`, `conversations/`, `products/`, `categories/`, `brands/`, `warehouses/`, `inventory/`, `attribute-templates/`, `image-match/`
- **Admin views**: `resources/views/admin/` (auth, dashboard, users CRUD, tenants CRUD, ai-system-prompt, business-categories CRUD)
- **Layouts**: `resources/views/layouts/app.blade.php` (public), `resources/views/layouts/tenant.blade.php` (tenant dashboard), `resources/views/admin/layouts/app.blade.php` (admin)
- **404 page**: `resources/views/errors/404.blade.php` — custom Bengali 404 with gradient styling
- **Menu config**: `config/menu.php` — add admin sidebar menu groups here
- **Tenancy config**: `config/tenancy.php` — central domains, DB suffix, tenant model
- **Services config**: `config/services.php` — Facebook OAuth + Groq + Cerebras + Gemini + CLIP server + Zernio base URL
- **Landlord migrations**: `database/migrations/`
- **Tenant migrations**: `database/migrations/tenant/`
- **Translation files**: `lang/bn/` and `lang/en/` (18 files each — sidebar, common, tenant, nav, dashboard, settings, integration, conversations, facebook, ai, image_match, products, categories, brands, warehouses, inventory, attributes, auth)
- **Localization middleware**: `app/Http/Middleware/SetLocale.php`
- **Domain blocking middleware**: `app/Http/Middleware/PreventAccessFromNonCentralDomains.php`
- **Language controller**: `app/Http/Controllers/Tenant/LanguageController.php`
- **Onboarding controller**: `app/Http/Controllers/OnboardingController.php`
- **Subdomain check controller**: `app/Http/Controllers/SubdomainController.php`
- **Dashboard controller**: `app/Http/Controllers/DashboardController.php` — handles dashboard, settings (profile/password/business/tone/pricing/faq/escalation), leads, reports, whatsapp, facebook, inventory, integration
- **Product controller**: `app/Http/Controllers/Dashboard/ProductController.php` — full CRUD with variant matrix, extra fields, conditional validation
- **Attribute template controller**: `app/Http/Controllers/Dashboard/AttributeTemplateController.php` — variant options + category extra field templates
- **Category observer**: `app/Observers/BusinessCategoryObserver.php` — auto-sync extra fields to tenants
- **Sync job**: `app/Jobs/SyncCategoryAttributeTemplates.php` — cross-tenant template UPSERT/soft-delete
- **Attribute template seeder**: `database/seeders/AttributeTemplateSeeder.php` — seeds variant options + extra fields

## Missing Views (routes exist, views don't)

These views are referenced in `routes/web.php` but **do not exist on disk**:
`tenant.facebook`

These views exist as placeholder pages:
`tenant.leads`, `tenant.reports`, `tenant.whatsapp`, `tenant.inventory`, `tenant.inventory-add`

`tenant.settings` exists at `resources/views/tenant/settings.blade.php` — includes profile, password, and full business settings (logo, delivery areas, payment methods, refund/exchange policies, order process message).

Visiting missing routes throws `ViewNotFoundException`.

## Database

Schema source of truth: migration files in `database/migrations/` (landlord) and `database/migrations/tenant/`.

### Landlord DB Tables
- `tenants` — string PK (subdomain), `data` JSON column (custom attributes like status, plan, trial_ends_at)
- `domains` — domain→tenant FK mapping. Used by `PreventAccessFromNonCentralDomains` to validate tenant domains
- `admins` — admin users (landlord DB, `admin` guard)
- `admin_user_permissions` — per-admin menu permissions (super_admin bypasses all)
- `ai_system_prompts` — global AI system prompt (landlord-level)
- `business_categories` — default business categories with `extra_fields` JSON (category-specific dynamic fields for onboarding)
- `cache`, `jobs`, `sessions` — Standard Laravel tables

### Tenant DB Tables
- `users` — tenant users (uses `#[Fillable]`/`#[Hidden]` PHP attributes, NOT `$fillable` array). Has `locale` column (default: `bn`) for language preference
- `facebook_settings` — per-user Facebook/Zernio connection config (connection_type enum: `facebook_app`/`zernio`)
- `ai_settings` — per-user AI API keys (type: `message`/`cerebras`/`image`)
- `conversations`, `messages` — chat history
- `categories` (self-FK parent_id), `attribute_templates` (is_variant_option boolean, is_active boolean, placeholder/default columns), `brands`, `products` (weight_kg column), `product_attribute_values`, `product_variants` (JSON attributes), `product_images`, `variant_images` (JSON embedding column)
- `warehouses`, `stock_movements`, `stock_transfers`, `inventory_alerts`, `attribute_options`
- `business_settings` — per-user business config from onboarding (category, tone, pricing, delivery areas (JSON array with name+price), payment methods (JSON array with name+details), advance payment settings, refund/exchange policies, order process message, FAQ, extra_fields_data JSON, logo_path)

### Seeder
- `AdminSeeder` creates `admin@socialboost.com` / `Admin@123456`, role `super_admin` (idempotent via `updateOrCreate`)
- `BusinessCategorySeeder` seeds 8 default categories (Fashion, Electronics, Food, Cosmetics, Furniture, Digital, Handicraft, Pharmacy) with category-specific extra_fields JSON
- `AttributeTemplateSeeder` — seeds 8 global variant options (Color, Size, Material, Storage, Flavor, Shade, Strength, Pack Size) + category-specific extra field templates from BusinessCategory JSON (uses `BusinessCategory::on('mysql')` for landlord DB access)

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
- **Registration validation** does NOT use `unique:users,email` (users are per-tenant)
- **Registration flow**: `GET /register` → redirects to `/onboarding` (no separate register page)
- **POST /register**: Removed — all registration goes through `OnboardingController@store`
- **Facebook OAuth**: HTTP not allowed — use ngrok HTTPS URL
- **Facebook test users**: Dev mode only testers/admins trigger webhooks
- **Horizon**: runs inside Docker worker container via Supervisor, not directly
- **Redis**: queue + cache driver in `.env` (`QUEUE_CONNECTION=redis`, `CACHE_STORE=redis`)
- **Stock operations** wrapped in `DB::transaction()` — `Product::recalculateStock()` called after variant changes
- **`InventoryAlert::isLowStock()`** — variant-aware (checks variant stock if variants exist)
- **Route conflicts**: Central `web.php` routes registered before tenant `tenant.php` routes. For identical paths, central version wins. Tenant routes rely on `InitializeTenancyByDomain` middleware to switch DB context.

## Product/Inventory Architecture

- **Variant Options** (Color/Size) vs **Product Attributes** (Material/Weight) — `attribute_templates.is_variant_option` boolean distinguishes them
- **Variant matrix**: JS `getCombinations()` generates cartesian product of options, auto-SKU format: `{PRODUCT-SKU}-{OPT1}-{OPT2}`
- **Variant attributes** stored as JSON: `{"Color": "Red", "Size": "M"}`
- **Product-level attributes** stored in `product_attribute_values` table
- **Stock transfers** create 2 `StockMovement` records (out + in) atomically

### Product Category Fields Plan (v5)

3-layer system for product fields:

**Layer 1 — Common Fields** (product-level, always present):
`name`, `sku`, `description`, `base_price`, `discount_price`, `stock_quantity`, `unit`, `barcode`, `status`, `is_featured`, `weight_kg`, `category_id`, `brand_id`, `sort_order`, `meta_title`, `meta_description`

**Layer 1B — Variant System** (optional, per-product):
- **Variant Options**: Global `attribute_templates` where `is_variant_option=true, is_global=true` (Color, Size, Material, etc.) — seeded by `AttributeTemplateSeeder`
- **Variant Matrix**: Alpine.js cartesian product of selected options → `product_variants` table
- **Auto-SKU**: `{PRODUCT-SKU}-{OPT1}-{OPT2}` format
- **Cap**: 50 soft warning (yellow), 100 hard block (red, submit disabled)
- **Stock auto-calc**: When variants exist, product-level `stock_quantity` is sum of all variant stocks

**Layer 2 — Category-Specific Extra Fields** (dynamic, synced from landlord):
- **Landlord**: `business_categories.extra_fields` JSON array defines fields per category (name, type, required, options, placeholder, default)
- **Tenant sync**: `AttributeTemplateSeeder` seeds extra field templates into tenant `attribute_templates` (linked by slug, `is_global=false, is_variant_option=false`)
- **Auto-sync**: `BusinessCategoryObserver` fires `SyncCategoryAttributeTemplates` job when `extra_fields` changes on create/update
- **Sync job**: UPSERTS template on extra_fields change, SOFT DELETES removed fields (sets `is_active=false`), HARD DELETES templates with no values
- **ProductController**: Resolves tenant Category via `Category::where('slug', $businessCategory->slug)->first()` — must use this FK, NOT `businessCategory->id`
- **Save**: `ProductAttributeValue` records per product (full replacement on update)
- **Validation**: Dynamic rules built from `extra_fields` JSON — `required` if `field.required=true`

**Conditional Validation**:
- Digital products (`digital-product`, `digital-product-service`): `unit` nullable, `stock_quantity` nullable
- When variants exist: `stock_quantity` skipped (auto-calculated from variants)
- `discount_price`: Must be `lte:base_price`

**Key Gotchas**:
- `BusinessCategory` lives in **landlord DB** — always `BusinessCategory::on('mysql')->find($id)`
- `BusinessSetting::category()` method already uses `on('mysql')` internally
- `attribute_templates.category_id` references **tenant** `categories.id`, NOT `business_categories.id`
- `AttributeTemplate.type` enum: `text|number|select|boolean|date|textarea` (textarea added via migration)
- PHP 8.4: Unparenthesized nested ternary `a ? b : c ? d : e` is **forbidden**
- Alpine.js: All reactive state (`hasVariants`, `showVariantModal`, `combinationCount`, `selectedOptions`) must be inside `x-data` scope
- Alpine.js: `x-if` must be on `<template>` tags only

### New Files (Product Category Fields)

- `app/Jobs/SyncCategoryAttributeTemplates.php` — Cross-tenant template sync (UPSERT/soft-delete/hard-delete)
- `app/Observers/BusinessCategoryObserver.php` — Triggers sync on `created` and `updated`
- `database/seeders/AttributeTemplateSeeder.php` — Seeds 8 global variant options + category-specific extra field templates
- `database/migrations/tenant/2026_07_16_000001_add_weight_kg_to_products_table.php`
- `database/migrations/tenant/2026_07_16_000002_add_is_active_to_attribute_templates_table.php`
- `database/migrations/tenant/2026_07_16_000003_add_placeholder_default_to_attribute_templates_table.php`
- `database/migrations/tenant/2026_07_16_000004_add_textarea_to_attribute_templates_type_enum.php`

### Modified Files (Product Category Fields)

- `app/Http/Controllers/Dashboard/ProductController.php` — Full rewrite: `create()`, `store()`, `edit()`, `update()`, `show()`, conditional validation, extra fields, variant matrix, tenant Category FK resolution
- `app/Models/Product.php` — Added `weight_kg` to `$fillable` and `$casts`
- `app/Models/AttributeTemplate.php` — Added `is_active` to `$fillable`/`$casts`, `placeholder`/`default` attributes
- `app/Models/BusinessSetting.php` — `category()` uses `on('mysql')`
- `app/Providers/AppServiceProvider.php` — Registers `BusinessCategoryObserver`
- `database/seeders/DatabaseSeeder.php` — Routes `AttributeTemplateSeeder` for tenant DBs
- `resources/views/tenant/products/create.blade.php` — Alpine.js variant matrix, extra fields, discount validation
- `resources/views/tenant/products/edit.blade.php` — Alpine.js variant modal, matrix, existing variants table, extra fields
- `resources/views/tenant/products/show.blade.php` — Extra fields display, variants table, correct field names
- `resources/lang/bn/products.php` — 14 new keys for product fields
- `resources/lang/en/products.php` — 14 new keys for product fields
- `resources/lang/bn/common.php` + `resources/lang/en/common.php` — Added `done` key

# Agent Instructions

Always reply in Banglish (Bengali written in English letters).
No matter what language the user writes in, always respond in Banglish.
Example: "Ei function ta fix korte hobe, karon ekhane error ache."

Always follow best practices for coding architecture (SOLID, DRY, KISS, YAGNI).
Follow Laravel conventions and the conventions of the frameworks/libraries used.
