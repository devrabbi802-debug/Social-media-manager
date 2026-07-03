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
- **Tenant DB** (`{subdomain}_socialboost`): `users`, `sessions`, `password_reset_tokens`, `facebook_settings`, `ai_settings`
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
- **`AiSystemPrompt`** — landlord-level table (`ai_system_prompts`), global default prompt with `{company_name}` placeholder
- **`AiSetting`** — tenant-level table (`ai_settings`), per-user `api_key` and optional `system_prompt`
- **`AiChatService`** (`app/Services/`) — Groq API wrapper, 15s timeout, handles 429 rate limiting
- **`SendAiReplyJob`** — queued on `facebook` queue, 3 tries, 45s backoff, `WithoutOverlapping` per tenant+sender. Sends typing indicators during AI processing
- **Queue**: Redis, queue name `facebook`, Horizon supervisor (1-10 processes, 256MB memory)

### Facebook Integration

- **Webhook route** in `routes/web.php` (central, NOT tenant) — Facebook calls via ngrok tunnel URL
- **CSRF exemption**: `webhook/*` excluded in `bootstrap/app.php`
- **OAuth flow**: redirect → callback → auto-fetch page_id + page_access_token → save to `facebook_settings`
- **Verify token**: `socialboost_verify_token_2026` (hardcoded in `FacebookOAuthController`)
- **Multi-tenant webhook**: `FacebookWebhookController` iterates ALL tenants to find matching `page_id` (O(n), won't scale well)
- **Ngrok URL check**: `wget -qO- http://127.0.0.1:4040/api/tunnels`
- **Facebook App ID**: `703674879507719`, permissions: `pages_show_list`, `pages_messaging`, `pages_read_engagement`

### Docker

```bash
docker compose up -d --build
docker compose down
docker compose logs -f
docker exec laravel-app php artisan <command>
```

- **6 services**: app, mysql (port 3307), node (port 5173), redis, phpmyadmin (port 8080), worker
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
- **Dashboard views**: `resources/views/dashboard/` — only `index`, `integration`, `facebook-settings`, `facebook-select-page`, `ai-setup` exist
- **Admin views**: `resources/views/admin/` (auth, dashboard, users CRUD, tenants CRUD, ai-system-prompt)
- **Layouts**: `resources/views/layouts/app.blade.php` (public), `resources/views/admin/layouts/app.blade.php` (admin)
- **Menu config**: `config/menu.php` — add admin sidebar menu groups here
- **Tenancy config**: `config/tenancy.php` — central domains, DB suffix, tenant model
- **Services config**: `config/services.php` — Facebook OAuth + Groq model
- **Landlord migrations**: `database/migrations/`
- **Tenant migrations**: `database/migrations/tenant/`

## Missing Views (routes exist, views don't)

These views are referenced in `routes/web.php` and `routes/tenant.php` but **do not exist on disk**:
`dashboard.settings`, `dashboard.leads`, `dashboard.reports`, `dashboard.whatsapp`, `dashboard.inventory`, `dashboard.inventory-add`, `dashboard.facebook`

Visiting these routes throws `ViewNotFoundException`.

## Database

### Tenant DB extra tables
- `ai_settings` — id, user_id (FK), api_key (hidden), system_prompt (nullable)

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

# Agent Instructions

Always reply in Banglish (Bengali written in English letters).
No matter what language the user writes in, always respond in Banglish.
Example: "Ei function ta fix korte hobe, karon ekhane error ache."

Always follow best practices for coding architecture (SOLID, DRY, KISS, YAGNI).
Follow Laravel conventions and the conventions of the frameworks/libraries used.
