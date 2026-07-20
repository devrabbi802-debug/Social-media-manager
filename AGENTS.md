# AGENTS.md

## Commands

```bash
composer setup     # install → .env → key → migrate → npm install → vite build
composer dev       # artisan serve + queue:listen + pail + vite (concurrently)
composer test      # config:clear + artisan test (SQLite :memory:)
php artisan tenants:migrate
php artisan tenants:seed

# Storefront React SPA
cd resources/storefront
npx vite build              # production build
npm run watch               # auto-rebuild on change
```

## Storefront React SPA (`resources/storefront/`)

- **Stack**: React 18, React Router 6, Vite 5, Tailwind v3 (own config), lucide-react icons
- **Separate `node_modules/`** from root — `.npmrc` sets `ignore-scripts=true`
- **Output**: `public/storefront/` — served by Laravel at `/{path?}` catch-all
- **Theme system**: `src/themes/` — currently only `clothing-fashion` active; `src/themes/index.js` maps slug → lazy import
- **Theme entry**: `src/themes/clothing-fashion/index.js` exports `{ Layout, Home, Products, ProductDetail, Category, Brand, Cart, Checkout, Auth, NotFound }`
- **App.jsx** loads theme via `loadTheme(slug)`, renders `Layout` wrapping lazy `<Routes>`

### Clothing-Fashion Theme (active)

All pages use **static data** — no API calls for storefront content.

| Route | Component | Description |
|---|---|---|
| `/` | Home | Slider, categories, best selling, new arrival, banner, jacket collection, features |
| `/products` | Products | Left sidebar filters (category/price/color/size/brand), grid, load more |
| `/products/:slug` | ProductDetail | Image gallery, color/size picker, qty, add to cart, wishlist |
| `/category/:slug` | Category | Products filtered by category slug |
| `/brand/:slug` | Brand | Products filtered by brand slug |
| `/cart` | Cart | Items list, qty control, coupon, order summary |
| `/checkout` | Checkout | Shipping form, SSLCOMMERZ/COD payment, order review |
| `/auth` | Auth | Phone+password login/register, Google login button |
| `*` | NotFound | 404 page |

### Components (`src/themes/clothing-fashion/components/`)

- **Header**: Fixed, transparent on home → white on scroll, mega menu (Men/Women/Kids/Collection with submenus), cart badge, search, user icon → `/auth`
- **Footer**: One Ummah BD branding, social links (FB/IG/YT/LI), quick links, legal, email subscribe, copyright
- **CartDrawer**: Right slide-in panel, overlay, items with qty, subtotal, view cart/checkout links
- **ProductCard**: 3:4 aspect ratio, hover add-to-cart button, wishlist heart, discount badge, black stars

### Contexts (`src/themes/clothing-fashion/contexts/`)

- **CartContext** — `{ items, drawerOpen, openDrawer, closeDrawer, addToCart, updateQuantity, removeItem, itemCount }`
- **WishlistContext** — `{ wishlist, toggleWishlist, isWishlisted }`
- **Layout.jsx** wraps `<CartProvider>` → `<WishlistProvider>` → `<ScrollToTop />` → content

## Build & Dev

- `npx vite build` in `resources/storefront/` — rebuild after ANY source change (static data edits included)
- Page route change auto-scrolls to top via `ScrollToTop` component in Layout
- `npm run watch` for auto-rebuild during dev

## Multi-Tenancy (Laravel Backend)

- **DB-per-tenant**: Landlord `socialboost` / Tenant `{subdomain}_socialboost`
- **Users table → tenant DB only** — never from central routes
- **Central domains**: `127.0.0.1`, `localhost`, `smm.test`, `socialboost.com`, `www.socialboost.com`
- **Route load order**: `web.php` → `admin.php` (central) → `tenant.php` (per-tenant)
- **Admin prefix**: `config('app.admin_panel_prefix', 'ax7k9m')` — `.env` uses `supermaster`
- **Dual auth guards**: `web` (User, tenant DB) + `admin` (Admin, landlord DB)
- **Middleware aliases**: `admin` → `AdminMiddleware`, `locale` → `SetLocale`, `central` → `PreventAccessFromNonCentralDomains`

## Docker

```bash
docker compose up -d --build
docker exec laravel-app php artisan <command>
```

7 services: app(8000), mysql(3307), node(5173), redis(6379), phpmyadmin(8080), worker(Horizon), clip-server(8089).

## Gotchas

- `.env` uses MySQL+Redis; `.env.example` defaults to PostgreSQL+`database` queue/cache
- Storefront edits require `npx vite build` — no HMR on Laravel dev server
- Route name conflict: `withRouting()` loads web.php AFTER tenant.php — central names overwrite tenant names
- `TenantCouldNotBeIdentifiedOnDomainException` → 404
- PHP 8.4: unparenthesized ternary `a ? b : c ? d : e` forbidden

## Communication

- Amar shathe kotha bolaar shomoy ALWAYS Banglish (Bengali + English mix) use korbo.
