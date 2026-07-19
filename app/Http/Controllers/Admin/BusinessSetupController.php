<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessSetup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BusinessSetupController extends Controller
{
    public function index()
    {
        $setup = BusinessSetup::getActive();

        return view('admin.business-setup.index', compact('setup'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'business_name' => 'nullable|string|max:255',
            'support_number' => 'nullable|string|max:50',
            'support_email' => 'nullable|email|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $setup = BusinessSetup::getActive();

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($setup->logo_path && Storage::disk('public')->exists($setup->logo_path)) {
                Storage::disk('public')->delete($setup->logo_path);
            }
            $validated['logo_path'] = $request->file('logo')->store('business-logos', 'public');
        }

        unset($validated['logo']);

        $setup->update($validated);

        return back()->with('success', 'বিজনেস সেটআপ আপডেট হয়েছে!');
    }
}
