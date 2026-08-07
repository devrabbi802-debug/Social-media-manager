<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function index()
    {
        $this->ensureDefaults();

        $roles = Role::withCount('users')->latest()->get();

        return view('tenant.roles.index', compact('roles'));
    }

    public function create()
    {
        $groups = config('tenant-permissions.groups');
        $permissionMap = config('tenant-permissions.permissions');

        return view('tenant.roles.create', compact('groups', 'permissionMap'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        Role::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']).'-'.Str::random(4),
            'description' => $data['description'] ?? null,
            'permissions' => $this->extractPermissions($request),
        ]);

        return redirect()->route('roles.index')->with('success', 'Role created successfully.');
    }

    public function edit(Role $role)
    {
        $groups = config('tenant-permissions.groups');
        $permissionMap = config('tenant-permissions.permissions');

        $rolePermissions = $role->permissionList();

        return view('tenant.roles.edit', compact('role', 'groups', 'permissionMap', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $data = $this->validated($request, $role);

        $role->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'permissions' => $this->extractPermissions($request),
        ]);

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        if ($role->users()->exists()) {
            return back()->with('error', 'This role is assigned to users and cannot be deleted.');
        }

        if ($role->is_system) {
            return back()->with('error', 'System roles cannot be deleted.');
        }

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }

    private function validated(Request $request, ?Role $role = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    private function extractPermissions(Request $request): array
    {
        $permissions = [];

        foreach ($request->input('permissions', []) as $slug => $actions) {
            foreach ($actions as $action => $value) {
                if ($value) {
                    $permissions[] = "{$slug}.{$action}";
                }
            }
        }

        return $permissions;
    }

    private function ensureDefaults(): void
    {
        if (Role::exists()) {
            return;
        }

        Role::create([
            'name' => 'Manager',
            'slug' => 'manager-'.substr(uniqid(), -4),
            'description' => 'Manages inventory, orders and day-to-day operations.',
            'permissions' => [
                'dashboard.list',
                'products.list', 'products.create', 'products.edit',
                'categories.list', 'categories.create', 'categories.edit',
                'brands.list', 'brands.create', 'brands.edit',
                'warehouses.list',
                'stock_movements.list', 'stock_movements.create',
                'stock_transfers.list', 'stock_transfers.create',
                'stock_alerts.list', 'stock_alerts.create', 'stock_alerts.edit',
                'attributes.list',
                'orders.list', 'orders.view', 'orders.edit', 'orders.export',
                'pos_terminal.list', 'pos_terminal.create',
                'pos_sales.list', 'pos_sales.view',
                'pos_sessions.list',
                'accounting.list',
                'accounting_money.list', 'accounting_money.create',
                'chart_of_accounts.list', 'chart_of_accounts.create', 'chart_of_accounts.edit',
                'journal_entries.list', 'journal_entries.create',
                'accounting_reports.list',
                'pos_reports.list',
                'purchase_reports.list',
                'sales_reports.list',
                'inventory_reports.list',
                'conversations.list', 'conversations.reply',
            ],
        ]);

        Role::create([
            'name' => 'Sales Agent',
            'slug' => 'sales-agent-'.substr(uniqid(), -4),
            'description' => 'Handles POS sales and conversations.',
            'permissions' => [
                'dashboard.list',
                'pos_terminal.list', 'pos_terminal.create',
                'pos_sales.list', 'pos_sales.view',
                'conversations.list', 'conversations.reply',
            ],
        ]);
    }
}
