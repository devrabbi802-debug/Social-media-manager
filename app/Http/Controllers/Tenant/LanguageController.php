<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function switch(Request $request)
    {
        $request->validate([
            'locale' => 'required|in:bn,en',
        ]);

        $user = $request->user();
        $user->update(['locale' => $request->locale]);

        app()->setLocale($request->locale);

        return redirect()->back();
    }
}
