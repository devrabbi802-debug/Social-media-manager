@php
    $businessSetup = \App\Models\BusinessSetup::getActive();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login - SocialBoost AI</title>
    <meta name="robots" content="noindex, nofollow">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        .login-gradient { background: linear-gradient(135deg, #059669 0%, #0d9488 50%, #0891b2 100%); }
    </style>
</head>
<body class="login-gradient min-h-screen flex items-center justify-center px-4">
    {{-- Decorative elements --}}
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
    </div>

    <div class="max-w-md w-full relative">
        {{-- Logo --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center mb-4">
                @if($businessSetup->getLogoUrl())
                    <img src="{{ $businessSetup->getLogoUrl() }}" alt="{{ $businessSetup->business_name ?? 'SocialBoost AI' }}" class="h-14 w-auto object-contain rounded-xl bg-white/10 backdrop-blur-sm p-2 border border-white/20">
                @else
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-white/10 backdrop-blur-sm rounded-2xl border border-white/20">
                        <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                @endif
            </div>
            <h1 class="text-2xl font-bold text-white">Admin Panel</h1>
        </div>

        {{-- Login Card --}}
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden border border-white/20">
            <div class="h-1 bg-gradient-to-r from-emerald-500 to-teal-500"></div>
            <div class="p-8">
                <h2 class="text-xl font-bold text-gray-900 text-center mb-6">Sign in to your account</h2>

                @if ($errors->any())
                    <div class="mb-6 bg-rose-50 border border-rose-200 text-rose-600 px-4 py-3 rounded-xl text-sm">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.login.submit') }}">
                    @csrf

                    <div class="mb-5">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                        <input type="email"
                               id="email"
                               name="email"
                               value="{{ old('email') }}"
                               required
                               autofocus
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors @error('email') border-rose-500 @enderror"
                               placeholder="admin@socialboost.com">
                    </div>

                    <div class="mb-5">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                        <input type="password"
                               id="password"
                               name="password"
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors @error('password') border-rose-500 @enderror"
                               placeholder="••••••••">
                    </div>

                    <div class="flex items-center mb-6">
                        <input type="checkbox"
                               name="remember"
                               id="remember"
                               class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                        <label for="remember" class="ml-2 text-sm text-gray-600">Remember me</label>
                    </div>

                    <button type="submit"
                            class="w-full bg-emerald-600 text-white py-3 rounded-xl font-semibold hover:bg-emerald-700 transition-all duration-200 shadow-lg">
                        Sign In
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-xs text-gray-400 flex items-center justify-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        This page is for administrators only
                    </p>
                </div>
            </div>
        </div>

        <p class="text-center text-emerald-200 text-sm mt-6">&copy; {{ date('Y') }} {{ $businessSetup->business_name }}</p>
    </div>
</body>
</html>
