# AGENTS.md

## Project Overview

Laravel 13.x social media manager + inventory management app. MySQL (MariaDB) backend, Vite + Tailwind CSS 4.x frontend. All UI text is in Bangla.

## Quick Start

```bash
# Full setup (install deps, generate key, migrate, build assets)
composer setup

# Start all dev services (server, queue, logs, vite)
composer dev
```

## Required Services

| Service | Port | Start Command |
|---------|------|---------------|
| MariaDB | 3306 | `sudo systemctl start mariadb` |
| Laravel | 8000 | `php artisan serve --host=0.0.0.0 --port=8000 &` |
| Vite | 5173 | `npm run dev` |

## Database

- **Connection:** MySQL (MariaDB)
- **Database name:** `social_media_manager`
- **User:** `root` (no password)

```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS social_media_manager;"
php artisan migrate
php artisan migrate:fresh --seed
```

## Testing

Tests use SQLite in-memory (no MySQL needed).

```bash
composer test                    # clear config + run all tests
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature
php artisan test --filter=ExampleTest
```

## Code Style

```bash
./vendor/bin/pint        # format
./vendor/bin/pint --fix  # fix + format
```

## Key Commands

| Command | Purpose |
|---------|---------|
| `composer setup` | Full project setup |
| `composer dev` | Start all dev services |
| `composer test` | Clear config + run tests |
| `npm run build` | Production assets |
| `npm run dev` | Vite dev server |

## Architecture

```
app/
  Http/
    Controllers/
      Controller.php                  → Abstract base (empty)
      Admin/
        UserController.php            → Admin user CRUD + permissions + impersonation
    Middleware/
      AdminMiddleware.php             → Admin guard auth check
  Models/
    User.php                          → End-user (name, email, password)
    Admin.php                         → Admin user (name, email, password, role)
    AdminUserPermission.php           → Per-menu permission rows (admin_id, menu_slug, permission)
  Providers/
    AppServiceProvider.php            → Empty

config/
  auth.php                            → Dual guard: web (users) + admin (admins)
  menu.php                            → Admin sidebar menu + permission definitions
  (+ standard Laravel configs)

database/
  migrations/
    users, sessions, password_reset_tokens
    cache, cache_locks
    jobs, job_batches, failed_jobs
    admins                            → Custom (id, name, email, password, role)
    admin_user_permissions            → Custom (admin_id, menu_slug, permission)
  seeders/
    DatabaseSeeder.php                → Creates test User + calls AdminSeeder
    AdminSeeder.php                   → Creates super_admin (admin@socialboost.com)

resources/views/
  layouts/app.blade.php               → Public layout (Bangla, Hind Siliguri font, CDN Tailwind)
  welcome.blade.php                   → Landing page
  features.blade.php                  → Features detail
  pricing.blade.php                   → Pricing + FAQ
  about.blade.php                     → About us
  contact.blade.php                   → Contact form + info
  auth/
    login.blade.php                   → User login
    register.blade.php                → User registration
  dashboard/
    index.blade.php                   → User dashboard (static/hardcoded data)
  admin/
    layouts/app.blade.php             → Admin layout (sidebar, permission-aware)
    auth/login.blade.php              → Admin login
    dashboard.blade.php               → Admin dashboard
    users/
      index.blade.php                 → Admin user list
      create.blade.php                → Create admin user + permissions
      edit.blade.php                  → Edit admin user + permissions

routes/
  web.php                             → Public + user auth + user dashboard (closures)
  admin.php                           → Admin auth + admin panel (prefix: /rootadmin)
  console.php                         → Just inspire command
```

## Gotchas

- **Auth routes are inline** in `routes/web.php` (POST login/register/logout), not in controllers. If adding auth features, edit `routes/web.php`.
- **Admin routes are inline** in `routes/admin.php` (closures) except UserController.
- **MariaDB must be running** for app to work. Tests don't need it.
- Vite watches `resources/` and ignores `storage/framework/views/`
- Run `php artisan config:clear` if config changes aren't taking effect
- `.env` has MySQL configured with `root` user, no password
- **Missing App\Services\Menu class** — `Admin::getAllPermissions()` references it for super_admin but it doesn't exist. Will throw error.
- **Registration discards phone/company** — validated but not saved (User model only has name, email, password fillable).
- **Missing views** — Routes reference 7 views that don't exist: dashboard.settings, dashboard.leads, dashboard.inventory, dashboard.reports, dashboard.whatsapp, dashboard.facebook, dashboard.inventory-add.
- **No controller-level auth checks** — Admin permissions only checked in Blade views (hide/show buttons), not in controllers. Any admin can hit routes directly.
- **Contact form has no backend** — HTML form exists but no POST route or handler.

## Role & Permission System

### Roles

| Role | Description |
|------|-------------|
| `super_admin` | Full access. Bypasses all permission checks. |
| `admin` | Granular per-menu, per-action permissions from `admin_user_permissions` table. |

### How Permissions Work

1. **Menu config** lives in `config/menu.php`. Each menu group has items with `slug`, `route`, `icon`, and `permissions` array.
2. **Permission types**: `list`, `create`, `edit`, `delete`, `view`, `login`
3. **Storage**: `admin_user_permissions` table stores rows: `(admin_id, menu_slug, permission)`.
4. **Checking**: Call `$admin->hasPermission('user_management', 'create')` — returns `true` if super_admin OR row exists.

### Adding New Permission Types (Buttons/Actions)

Jokhon kono notun button ba action add koro admin panel e (jemon: Show, Export Excel, Approve, Print, Share, Duplicate, Archive, etc.), tahole **must** `config/menu.php` te o permission add korte hobe. Na hole permission check kaj korbe na.

**Step 1:** `config/menu.php` te relevant menu item er `permissions` array te notun permission add koro:

```php
'items' => [
    [
        'slug'       => 'lead_management',
        'title'      => 'Leads',
        'route'      => 'admin.leads.index',
        'icon'       => '<path .../>',
        'permissions' => ['list', 'create', 'edit', 'delete', 'view', 'export', 'approve', 'print'],
        //                                                       ↑ existing  ↑ new buttons er permission
    ],
],
```

**Step 2:** `config/menu.php` er `permissions` array te notun permission er label add koro:

```php
'permissions' => [
    'list'    => 'View List',
    'create'  => 'Create',
    'edit'    => 'Edit',
    'delete'  => 'Delete',
    'view'    => 'View Details',
    'login'   => 'Login as User',
    'export'  => 'Export Excel',     // ← notun
    'approve' => 'Approve',          // ← notun
    'print'   => 'Print',            // ← notun
],
```

**Step 3:** Controller te permission check koro:

```php
public function export(Request $request)
{
    $admin = Auth::guard('admin')->user();
    if (! $admin->hasPermission('lead_management', 'export')) {
        abort(403);
    }
    // export logic...
}
```

**Step 4:** Blade view te button conditionally show koro:

```blade
@if($adminUser->hasPermission('lead_management', 'export'))
    <a href="{{ route('admin.leads.export') }}" class="btn">
        Export Excel
    </a>
@endif

@if($adminUser->hasPermission('lead_management', 'approve'))
    <form method="POST" action="{{ route('admin.leads.approve', $lead) }}">
        @csrf
        <button type="submit">Approve</button>
    </form>
@endif
```

**Step 5:** Admin create/edit form e permission checkbox automatically add hobe karon `config/menu.php` theke dynamically generate hoy.

**Common New Permission Examples:**

| Permission | Kothay Use | Description |
|------------|-----------|-------------|
| `export` | Export/Download buttons | Excel, CSV, PDF download |
| `approve` | Approve/Reject buttons | Lead, order, content approval |
| `print` | Print button | Invoice, report print |
| `share` | Share buttons | Social media share |
| `duplicate` | Copy/Duplicate button | Product, template duplicate |
| `archive` | Archive/Restore button | Soft delete + restore |
| `bulk_delete` | Bulk delete checkbox | Multiple items delete |
| `bulk_export` | Bulk export | Multiple items export |
| `send` | Send/Reply buttons | WhatsApp, email send |
| `publish` | Publish/Unpublish | Content publish toggle |
| `assign` | Assign to user | Lead, task assignment |

**IMPORTANT:** Notun permission add korar por `php artisan config:clear` chalao jate config cache clear hoy.

### Adding a New Menu/Feature to Admin Panel

**Step 1:** Add menu item to `config/menu.php`:

```php
[
    'id'    => 'lead_management',  // group id
    'title' => 'Lead Management',
    'items' => [
        [
            'slug'       => 'lead_management',
            'title'      => 'Leads',
            'route'      => 'admin.leads.index',
            'icon'       => '<path .../>',  // SVG path
            'permissions' => ['list', 'create', 'edit', 'delete', 'view'],
        ],
    ],
],
```

**Step 2:** Create controller, routes, and views following the `UserController` pattern.

**Step 3:** Check permissions in controller:

```php
public function index()
{
    $admin = Auth::guard('admin')->user();
    if (! $admin->hasPermission('lead_management', 'list')) {
        abort(403);
    }
    // ...
}
```

**Step 4:** In Blade views, conditionally show buttons:

```blade
@if($adminUser->hasPermission('lead_management', 'create'))
    <a href="{{ route('admin.leads.create') }}">Add Lead</a>
@endif
```

**Step 5:** Run migration if new tables needed, then `php artisan migrate`.

### Permission Checking Methods (Admin Model)

```php
$admin->hasPermission(string $menuSlug, string $permission): bool
$admin->getAllPermissions(): array                    // e.g. ['user_management.list', 'user_management.create']
$admin->getPermissionsBySlug(string $menuSlug): array // e.g. ['list', 'create', 'edit']
```

### Impersonation (Login As)

Super admin can impersonate any admin user via `POST /rootadmin/users/{admin}/login-as` (handled by `UserController@loginAs`).

### Seeder

Default super_admin credentials: `admin@socialboost.com` / `Admin@123456` (created by `AdminSeeder`).

## Agent Instructions

Always reply in Banglish (Bengali written in English letters).
No matter what language the user writes in, always respond in Banglish.
Example: "Ei function ta fix korte hobe, karon ekhane error ache."
