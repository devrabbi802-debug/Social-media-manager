<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SocialBoost AI - সোশ্যাল মিডিয়া ম্যানেজমেন্ট প্ল্যাটফর্ম')</title>
    <meta name="description" content="AI-চালিত সোশ্যাল মিডিয়া ম্যানেজমেন্ট এবং ইনভেন্টরি ম্যানেজমেন্ট প্ল্যাটফর্ম">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Hind Siliguri', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .gradient-text { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        .float-animation { animation: float 6s ease-in-out infinite; }
        @keyframes float { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-20px); } }
        .pulse-glow { animation: pulse-glow 2s ease-in-out infinite; }
        @keyframes pulse-glow { 0%, 100% { box-shadow: 0 0 20px rgba(102, 126, 234, 0.4); } 50% { box-shadow: 0 0 40px rgba(102, 126, 234, 0.8); } }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50 text-gray-900 antialiased">
    {{-- Navigation --}}
    <nav class="bg-white shadow-sm sticky top-0 z-50" x-data="{ mobileMenuOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="flex items-center space-x-2">
                        <div class="w-10 h-10 gradient-bg rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <span class="text-xl font-bold gradient-text">SocialBoost AI</span>
                    </a>
                </div>

                <div class="hidden md:flex items-center space-x-8">
                    <a href="{{ url('/') }}" class="text-gray-600 hover:text-purple-600 transition font-medium">হোম</a>
                    <a href="{{ url('/features') }}" class="text-gray-600 hover:text-purple-600 transition font-medium">ফিচার</a>
                    <a href="{{ url('/pricing') }}" class="text-gray-600 hover:text-purple-600 transition font-medium">মূল্য</a>
                    <a href="{{ url('/about') }}" class="text-gray-600 hover:text-purple-600 transition font-medium">আমাদের সম্পর্কে</a>
                    <a href="{{ url('/contact') }}" class="text-gray-600 hover:text-purple-600 transition font-medium">যোগাযোগ</a>
                </div>

                <div class="hidden md:flex items-center space-x-4">
                    @auth
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" @click.outside="open = false" class="flex items-center space-x-1 text-gray-600 hover:text-purple-600 transition font-medium">
                                <svg class="w-8 h-8 bg-purple-100 rounded-full p-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="open" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border py-2 z-50">
                                <a href="{{ url('/dashboard') }}" class="block px-4 py-2 text-gray-700 hover:bg-purple-50 hover:text-purple-600 transition">ড্যাশবোর্ড</a>
                                <hr class="my-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50 transition">লগআউট</button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ url('/login') }}" class="text-gray-600 hover:text-purple-600 transition font-medium">লগইন</a>
                        <a href="{{ url('/onboarding') }}" class="gradient-bg text-white px-6 py-2 rounded-full font-medium hover:opacity-90 transition">Let's Start</a>
                    @endauth
                </div>

                <div class="md:hidden flex items-center">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-gray-600 hover:text-purple-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div x-show="mobileMenuOpen" x-transition class="md:hidden bg-white border-t">
            <div class="px-4 py-3 space-y-3">
                <a href="{{ url('/') }}" class="block text-gray-600 hover:text-purple-600">হোম</a>
                <a href="{{ url('/features') }}" class="block text-gray-600 hover:text-purple-600">ফিচার</a>
                <a href="{{ url('/pricing') }}" class="block text-gray-600 hover:text-purple-600">মূল্য</a>
                <a href="{{ url('/about') }}" class="block text-gray-600 hover:text-purple-600">আমাদের সম্পর্কে</a>
                <a href="{{ url('/contact') }}" class="block text-gray-600 hover:text-purple-600">যোগাযোগ</a>
                <hr>
                @auth
                    <a href="{{ url('/dashboard') }}" class="block text-purple-600 font-medium">ড্যাশবোর্ড</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left text-red-600 font-medium">লগআউট</button>
                    </form>
                @else
                    <a href="{{ url('/login') }}" class="block text-gray-600">লগইন</a>
                    <a href="{{ url('/onboarding') }}" class="block gradient-bg text-white px-6 py-2 rounded-full text-center font-medium">Let's Start</a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- Main Content --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-gray-900 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center space-x-2 mb-4">
                        <div class="w-10 h-10 gradient-bg rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <span class="text-xl font-bold">SocialBoost AI</span>
                    </div>
                    <p class="text-gray-400">AI-চালিত সোশ্যাল মিডিয়া ম্যানেজমেন্ট এবং ইনভেন্টরি ম্যানেজমেন্ট প্ল্যাটফর্ম</p>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">পণ্য</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="{{ url('/features') }}" class="hover:text-white transition">ফিচার</a></li>
                        <li><a href="{{ url('/pricing') }}" class="hover:text-white transition">মূল্য</a></li>
                        <li><a href="#" class="hover:text-white transition">ইন্টিগ্রেশন</a></li>
                        <li><a href="#" class="hover:text-white transition">API</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">কোম্পানি</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="{{ url('/about') }}" class="hover:text-white transition">আমাদের সম্পর্কে</a></li>
                        <li><a href="{{ url('/contact') }}" class="hover:text-white transition">যোগাযোগ</a></li>
                        <li><a href="#" class="hover:text-white transition">ব্লগ</a></li>
                        <li><a href="#" class="hover:text-white transition">ক্যারিয়ার</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">সাপোর্ট</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white transition">হেল্প সেন্টার</a></li>
                        <li><a href="#" class="hover:text-white transition">ডকুমেন্টেশন</a></li>
                        <li><a href="#" class="hover:text-white transition">প্রাইভেসি পলিসি</a></li>
                        <li><a href="#" class="hover:text-white transition">টার্মস অব সার্ভিস</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-12 pt-8 text-center text-gray-400">
                <p>&copy; {{ date('Y') }} SocialBoost AI. সর্বস্বত্ব সংরক্ষিত।</p>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
