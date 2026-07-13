@php
    $adminUser = Auth::guard('admin')->user();
    $menuGroups = config('menu.groups');
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin Panel - SocialBoost AI')</title>
    <meta name="robots" content="noindex, nofollow">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
        .sidebar-item-active { background: rgba(16, 185, 129, 0.1); color: #34d399; border-left: 2px solid #34d399; }
        .sidebar-item { color: #94a3b8; border-left: 2px solid transparent; }
        .sidebar-item:hover { background: rgba(255, 255, 255, 0.05); color: #ffffff; }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50" x-data="{ sidebarOpen: true, mobileMenuOpen: false }">
    {{-- Sidebar --}}
    <aside :class="mobileMenuOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'" class="fixed left-0 top-0 bottom-0 w-64 bg-slate-900 text-white z-50 transform transition-transform duration-300 overflow-y-auto">
        {{-- Logo --}}
        <div class="h-16 flex items-center px-5 border-b border-white/10">
            <div class="w-9 h-9 bg-emerald-500 rounded-xl flex items-center justify-center mr-3">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <div>
                <span class="text-sm font-bold tracking-tight">SocialBoost</span>
                <span class="block text-slate-400 font-medium" style="font-size: 10px; text-transform: uppercase; letter-spacing: 0.1em">Admin Panel</span>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="p-3 space-y-1">
            @foreach($menuGroups as $group)
                @php
                    $visibleItems = collect($group['items'])->filter(function($item) use ($adminUser) {
                        return $adminUser->hasPermission($item['slug'], 'list');
                    });
                @endphp

                @if($visibleItems->isEmpty())
                    @continue
                @endif

                @if($group['title'])
                    <div class="pt-4 pb-2 px-3 text-slate-500 font-semibold uppercase" style="font-size: 10px; letter-spacing: 0.1em">{{ $group['title'] }}</div>
                @endif

                @foreach($visibleItems as $item)
                    @php
                        $isActive = request()->routeIs($item['route']);
                    @endphp
                    <a href="{{ route($item['route']) }}" class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ $isActive ? 'sidebar-item-active' : 'sidebar-item' }}">
                        <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $item['icon'] !!}</svg>
                        {{ $item['title'] }}
                    </a>
                @endforeach
            @endforeach
        </nav>

        {{-- Sidebar Footer --}}
        <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-white/10">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-emerald-500 rounded-lg flex items-center justify-center text-white text-xs font-bold">
                    {{ mb_substr($adminUser->name ?? 'A', 0, 1, 'UTF-8') }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-medium text-white truncate">{{ $adminUser->name ?? 'Admin' }}</p>
                    <p class="text-slate-400 truncate" style="font-size: 10px">{{ $adminUser->role ?? 'admin' }}</p>
                </div>
            </div>
        </div>
    </aside>

    {{-- Sidebar Overlay (Mobile) --}}
    <div x-show="mobileMenuOpen" @click="mobileMenuOpen = false" x-transition.opacity class="fixed inset-0 bg-black bg-opacity-60 z-40 md:hidden"></div>

    {{-- Main Content --}}
    <div class="md:ml-64 min-h-screen">
        {{-- Top Bar --}}
        <header class="sticky top-0 z-30 bg-white border-b border-gray-200 h-16 flex items-center px-4 sm:px-6">
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden mr-3 text-gray-500 hover:text-gray-700 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <div class="flex-1"></div>

            <div class="flex items-center space-x-3">
                <span class="text-sm text-gray-600 hidden sm:inline">{{ $adminUser->name ?? 'Admin' }}</span>
                <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center text-emerald-600 text-xs font-bold">
                    {{ mb_substr($adminUser->name ?? 'A', 0, 1, 'UTF-8') }}
                </div>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-gray-400 hover:text-gray-600 transition flex items-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </header>

        {{-- Page Content --}}
        <main class="p-4 sm:p-6">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
