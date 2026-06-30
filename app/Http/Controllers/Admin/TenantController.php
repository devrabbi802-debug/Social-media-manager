<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::all();
        return view('admin.tenants.index', compact('tenants'));
    }

    public function create()
    {
        return view('admin.tenants.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|string|min:3|max:50|regex:/^[a-z0-9-]+$/|unique:tenants,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:tenants,email',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'plan' => 'required|in:trial,basic,pro,enterprise',
        ]);

        $tenant = Tenant::create($validated);
        $tenant->domains()->create([
            'domain' => $validated['id'] . '.' . config('app.domain'),
        ]);

        return redirect()->route('admin.tenants.index')
            ->with('success', 'Tenant created successfully!');
    }

    public function edit(Tenant $tenant)
    {
        return view('admin.tenants.edit', compact('tenant'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:tenants,email,' . $tenant->id,
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'plan' => 'required|in:trial,basic,pro,enterprise',
            'status' => 'required|in:active,suspended,trial',
        ]);

        $tenant->update($validated);

        return redirect()->route('admin.tenants.index')
            ->with('success', 'Tenant updated successfully!');
    }

    public function destroy(Tenant $tenant)
    {
        $tenant->domains()->delete();
        $tenant->delete();

        return redirect()->route('admin.tenants.index')
            ->with('success', 'Tenant deleted successfully!');
    }
}
