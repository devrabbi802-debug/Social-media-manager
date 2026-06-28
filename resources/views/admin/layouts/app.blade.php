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
    </style>
    @stack('styles')
</head>
<body class="bg-gray-100" x-data="{ sidebarOpen: true, mobileMenuOpen: false }">
    {{-- Top Bar --}}
    <div class="fixed top-0 left-0 right-0 z-50 bg-indigo-950 text-white h-14 flex items-center px-4 shadow-lg">
        <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden mr-3 text-white/70 hover:text-white">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <div class="flex items-center space-x-2">
            <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <span class="text-lg font-bold hidden sm:inline">Admin Panel</span>
        </div>

        <div class="flex-1"></div>

        <div class="flex items-center space-x-4">
            <span class="text-sm text-indigo-200 hidden sm:inline">{{ $adminUser->name ?? 'Admin' }}</span>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="text-sm text-indigo-200 hover:text-white transition flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Logout
                </button>
            </form>
        </div>
    </div>

    {{-- Sidebar Overlay (Mobile) --}}
    <div x-show="mobileMenuOpen" @click="mobileMenuOpen = false" x-transition.opacity class="fixed inset-0 bg-black/50 z-40 md:hidden"></div>

    {{-- Sidebar --}}
    <aside :class="mobileMenuOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'" class="fixed left-0 top-14 bottom-0 w-64 bg-indigo-950 text-white z-50 transform transition-transform duration-200 overflow-y-auto">
        <nav class="p-4 space-y-1">
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
                    <div class="pt-4 pb-2 px-4 text-xs font-semibold text-indigo-400 uppercase tracking-wider">{{ $group['title'] }}</div>
                @endif

                @foreach($visibleItems as $item)
                    @php
                        $isActive = request()->routeIs($item['route']);
                    @endphp
                    <a href="{{ route($item['route']) }}" class="flex items-center px-4 py-3 rounded-lg transition {{ $isActive ? 'bg-indigo-600 text-white' : 'text-indigo-200 hover:bg-indigo-800/50 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $item['icon'] !!}</svg>
                        {{ $item['title'] }}
                    </a>
                @endforeach
            @endforeach
        </nav>
    </aside>

    {{-- Main Content --}}
    <main class="md:ml-64 pt-14 min-h-screen">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
