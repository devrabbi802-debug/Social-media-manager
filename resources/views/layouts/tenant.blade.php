@php
    $tenantUser = Auth::user();
    $businessLogo = \App\Models\BusinessSetting::where('user_id', $tenantUser->id ?? 0)->first();
@endphp

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard - SocialBoost AI')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Hind Siliguri', sans-serif; }
        [x-cloak] { display: none !important; }
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-100" x-data="{ sidebarOpen: true, mobileMenuOpen: false }">
    {{-- Top Bar --}}
    <div class="fixed top-0 left-0 right-0 z-50 bg-white shadow-sm h-14 flex items-center px-4 border-b border-gray-200">
        <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden mr-3 text-gray-500 hover:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <div class="flex items-center space-x-2">
            <a href="{{ route('dashboard') }}">
                <img src="{{ asset('images/logo.png') }}" alt="SocialBoost AI" class="h-8 w-auto object-contain">
            </a>
        </div>

        <div class="flex-1"></div>

        <div class="flex items-center space-x-2">
            {{-- Language Switcher --}}
            <div class="relative" x-data="{ langOpen: false }">
                <button @click="langOpen = !langOpen" @click.outside="langOpen = false" class="flex items-center space-x-1 text-gray-600 hover:text-purple-600 transition px-2 py-1.5 rounded-lg hover:bg-gray-50">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                    </svg>
                    <span class="text-sm font-medium hidden sm:inline">{{ app()->getLocale() === 'bn' ? 'বাংলা' : 'English' }}</span>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="langOpen" x-transition x-cloak class="absolute right-0 mt-2 w-40 bg-white rounded-xl shadow-lg border py-2 z-50">
                    <form method="POST" action="{{ route('language.switch') }}">
                        @csrf
                        <input type="hidden" name="locale" value="bn">
                        <button type="submit" class="w-full text-left px-4 py-2 text-sm hover:bg-purple-50 transition flex items-center space-x-2 {{ app()->getLocale() === 'bn' ? 'text-purple-600 font-semibold bg-purple-50' : 'text-gray-700' }}">
                            <span>বাং</span>
                            <span>বাংলা</span>
                        </button>
                    </form>
                    <form method="POST" action="{{ route('language.switch') }}">
                        @csrf
                        <input type="hidden" name="locale" value="en">
                        <button type="submit" class="w-full text-left px-4 py-2 text-sm hover:bg-purple-50 transition flex items-center space-x-2 {{ app()->getLocale() === 'en' ? 'text-purple-600 font-semibold bg-purple-50' : 'text-gray-700' }}">
                            <span>EN</span>
                            <span>English</span>
                        </button>
                    </form>
                </div>
            </div>

            {{-- User Dropdown --}}
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" @click.outside="open = false" class="flex items-center space-x-2 text-gray-600 hover:text-purple-600 transition">
                    @if($businessLogo && $businessLogo->logo_path)
                        <img src="{{ $businessLogo->getLogoUrl() }}" alt="Logo" class="w-8 h-8 rounded-full object-cover border border-gray-200">
                    @else
                        <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                            <span class="text-purple-600 font-bold text-sm">{{ mb_substr($tenantUser->name ?? 'U', 0, 1) }}</span>
                        </div>
                    @endif
                    <span class="text-sm font-medium hidden sm:inline">{{ $tenantUser->name ?? 'User' }}</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open" x-transition x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border py-2 z-50">
                    <div class="px-4 py-2 border-b">
                        <p class="text-sm font-medium text-gray-900">{{ $tenantUser->name ?? '' }}</p>
                        <p class="text-xs text-gray-500">{{ $tenantUser->email ?? '' }}</p>
                    </div>
                    <form method="POST" action="{{ route('language.switch') }}">
                        @csrf
                        <input type="hidden" name="locale" value="{{ app()->getLocale() === 'bn' ? 'en' : 'bn' }}">
                        <button type="submit" class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-50 transition text-sm flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3"/>
                            </svg>
                            <span>{{ app()->getLocale() === 'bn' ? 'Switch to English' : 'বাংলায় পরিবর্তন করুন' }}</span>
                        </button>
                    </form>
                    <div class="border-t"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50 transition text-sm">@lang('common.logout')</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Sidebar Overlay (Mobile) --}}
    <div x-show="mobileMenuOpen" @click="mobileMenuOpen = false" x-transition.opacity class="fixed inset-0 bg-black/50 z-40 md:hidden"></div>

    {{-- Sidebar --}}
    <aside :class="mobileMenuOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'" class="fixed left-0 top-14 bottom-0 w-64 bg-white border-r border-gray-200 z-50 transform transition-transform duration-200 overflow-y-auto">
        <nav class="p-4 space-y-1">
            @php
                $menuItems = [
                    ['label' => __('sidebar.dashboard'), 'route' => 'dashboard', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>'],
                    ['label' => __('sidebar.integration'), 'route' => 'integration', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>'],
                    ['label' => __('sidebar.ai_setup'), 'route' => 'ai.setup', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>'],
                    ['label' => __('sidebar.conversations'), 'route' => 'conversations', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>'],
                    ['label' => __('sidebar.image_match'), 'route' => 'image-match.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>'],
                ];

                $inventoryItems = [
                    ['label' => __('sidebar.inventory_dashboard'), 'route' => 'inventory.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>'],
                    ['label' => __('sidebar.products'), 'route' => 'inventory.products.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>'],
                    ['label' => __('sidebar.categories'), 'route' => 'inventory.categories.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>'],
                    ['label' => __('sidebar.brands'), 'route' => 'inventory.brands.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>'],
                    ['label' => __('sidebar.warehouses'), 'route' => 'inventory.warehouses.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>'],
                    ['label' => __('sidebar.stock_movements'), 'route' => 'inventory.movements', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>'],
                    ['label' => __('sidebar.stock_transfers'), 'route' => 'inventory.transfers.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>'],
                    ['label' => __('sidebar.stock_alerts'), 'route' => 'inventory.alerts', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>'],
                    ['label' => __('sidebar.attributes'), 'route' => 'inventory.attributes.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>'],
                ];

                $orderItems = [
                    ['label' => __('sidebar.orders'), 'route' => 'orders.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>'],
                ];

                $webSetupItems = [
                    ['label' => __('sidebar.storefront_preview'), 'url' => '/', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>', 'target' => '_blank'],
                    ['label' => __('sidebar.storefront_settings'), 'route' => 'storefront-settings.index', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>'],
                ];
            @endphp

            {{-- Main Menu --}}
            @foreach($menuItems as $item)
                @php
                    $isActive = request()->routeIs($item['route']);
                @endphp
                <a href="{{ route($item['route']) }}" class="flex items-center px-4 py-3 rounded-lg transition text-sm {{ $isActive ? 'bg-purple-50 text-purple-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $item['icon'] !!}</svg>
                    {{ $item['label'] }}
                </a>
            @endforeach

            {{-- Inventory Section --}}
            <div class="pt-4 pb-2 px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">@lang('sidebar.inventory')</div>
            @foreach($inventoryItems as $item)
                @php
                    $isActive = request()->routeIs($item['route']);
                @endphp
                <a href="{{ route($item['route']) }}" class="flex items-center px-4 py-2.5 rounded-lg transition text-sm {{ $isActive ? 'bg-purple-50 text-purple-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <svg class="w-4 h-4 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $item['icon'] !!}</svg>
                    {{ $item['label'] }}
                </a>
            @endforeach

            {{-- Orders Section --}}
            <div class="pt-4 pb-2 px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider border-t border-gray-100 mt-4">@lang('sidebar.order_management')</div>
            @foreach($orderItems as $item)
                @php
                    $isActive = request()->routeIs($item['route']);
                @endphp
                <a href="{{ route($item['route']) }}" class="flex items-center px-4 py-2.5 rounded-lg transition text-sm {{ $isActive ? 'bg-purple-50 text-purple-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <svg class="w-4 h-4 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $item['icon'] !!}</svg>
                    {{ $item['label'] }}
                </a>
            @endforeach

            {{-- Web Setup Section --}}
            <div class="pt-4 pb-2 px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider border-t border-gray-100 mt-4">@lang('sidebar.web_setup')</div>
            @foreach($webSetupItems as $item)
                @php
                    $isActive = isset($item['route']) ? request()->routeIs($item['route']) : false;
                    $target = $item['target'] ?? '_self';
                    $url = isset($item['route']) ? route($item['route']) : $item['url'];
                @endphp
                <a href="{{ $url }}" target="{{ $target }}" class="flex items-center px-4 py-2.5 rounded-lg transition text-sm {{ $isActive ? 'bg-purple-50 text-purple-700 font-semibold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <svg class="w-4 h-4 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $item['icon'] !!}</svg>
                    {{ $item['label'] }}
                </a>
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
