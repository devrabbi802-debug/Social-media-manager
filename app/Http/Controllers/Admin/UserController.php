<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminUserPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        $users = Admin::latest()->get();
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $menuGroups = config('menu.groups');
        return view('admin.users.create', compact('menuGroups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:admins',
            'password' => 'required|string|min:6|confirmed',
            'role'     => 'required|in:super_admin,admin',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $admin = Admin::create($validated);

        $this->syncPermissions($admin, $request);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function edit(Admin $user)
    {
        $menuGroups = config('menu.groups');
        $userPermissions = $user->permissions->groupBy('menu_slug')
            ->map(fn($perms) => $perms->pluck('permission')->toArray())
            ->toArray();
        return view('admin.users.edit', compact('user', 'menuGroups', 'userPermissions'));
    }

    public function update(Request $request, Admin $user)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:admins,email,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'role'     => 'required|in:super_admin,admin',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        $user->permissions()->delete();
        $this->syncPermissions($user, $request);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(Admin $user)
    {
        if ($user->role === 'super_admin') {
            return back()->with('error', 'Super Admin cannot be deleted.');
        }

        $user->permissions()->delete();
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }

    public function loginAs(Admin $admin)
    {
        Auth::guard('admin')->login($admin);

        return redirect('/rootadmin/dashboard');
    }

    private function syncPermissions(Admin $admin, Request $request): void
    {
        $permissions = $request->input('permissions', []);

        foreach ($permissions as $menuSlug => $actions) {
            foreach ($actions as $action => $value) {
                if ($value) {
                    AdminUserPermission::create([
                        'admin_id'   => $admin->id,
                        'menu_slug'  => $menuSlug,
                        'permission' => $action,
                    ]);
                }
            }
        }
    }
}
