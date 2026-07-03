@extends('layouts.app')

@section('title', 'AI সেটআপ - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Dashboard Header --}}
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">AI সেটআপ</h1>
                    <p class="text-gray-600">আপনার AI API Key পরিচালনা করুন — একাধিক Key যোগ করুন, একটি লিমিট শেষ হলে পরবর্তী দিয়ে কাজ চলবে</p>
                </div>
                <div class="flex items-center space-x-4">
                    @if($aiSettings->isNotEmpty())
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                            {{ $aiSettings->where('is_active', true)->count() }} সক্রিয়
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                            <span class="w-2 h-2 bg-yellow-500 rounded-full mr-2"></span>
                            অসংযুক্ত
                        </span>
                    @endif
                    <a href="{{ url('/integration') }}" class="text-gray-600 hover:text-purple-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page Navigation --}}
        <div class="mb-8 border-b border-gray-200">
            <nav class="flex space-x-8">
                <a href="{{ route('dashboard') }}" class="whitespace-nowrap py-4 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium text-sm transition">
                    ড্যাশবোর্ড
                </a>
                <a href="{{ route('integration') }}" class="whitespace-nowrap py-4 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium text-sm transition">
                    সোশ্যাল মিডিয়া ইন্টিগ্রেশন
                </a>
                <a href="{{ route('ai.setup') }}" class="whitespace-nowrap py-4 px-1 border-b-2 border-purple-600 text-purple-600 font-medium text-sm transition">
                    AI সেটআপ
                </a>
            </nav>
        </div>

        {{-- Success/Error Messages --}}
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Add New Key Form --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mr-4">
                            <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">নতুন API Key যোগ করুন</h2>
                            <p class="text-sm text-gray-500">Groq API Key যোগ করুন — একাধিক Key যোগ করতে পারবেন</p>
                        </div>
                    </div>

                    <form action="{{ route('ai.setup.store') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="api_key" class="block text-sm font-medium text-gray-700 mb-2">API Key *</label>
                            <input
                                type="password"
                                id="api_key"
                                name="api_key"
                                value="{{ old('api_key') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition font-mono text-sm"
                                placeholder="gsk_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                                required
                            >
                        </div>
                        <button type="submit" class="inline-flex items-center px-6 py-3 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition shadow-lg shadow-purple-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Key যোগ করুন
                        </button>
                    </form>
                </div>

                {{-- Existing Keys List --}}
                @if($aiSettings->isNotEmpty())
                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <div class="flex items-center mb-6">
                            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mr-4">
                                <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">আপনার API Keys ({{ $aiSettings->count() }})</h2>
                                <p class="text-sm text-gray-500">অগ্রাধিকার অনুযায়ী সাজানো — উপরের Key আগে ব্যবহার হবে</p>
                            </div>
                        </div>

                        <div class="space-y-3" id="keys-list">
                            @foreach($aiSettings as $index => $setting)
                                <div class="border border-gray-200 rounded-xl p-4 {{ $setting->is_active ? 'bg-white' : 'bg-gray-50 opacity-60' }}">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-4">
                                            {{-- Priority Number --}}
                                            <div class="w-10 h-10 {{ $setting->is_active ? 'bg-purple-100' : 'bg-gray-200' }} rounded-full flex items-center justify-center flex-shrink-0">
                                                <span class="text-sm font-bold {{ $setting->is_active ? 'text-purple-600' : 'text-gray-500' }}">#{{ $index + 1 }}</span>
                                            </div>

                                            {{-- Key Info --}}
                                            <div>
                                                <div class="flex items-center space-x-2">
                                                    <span class="font-medium text-gray-900">Key #{{ $index + 1 }}</span>
                                                    @if($setting->is_active)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">সক্রিয়</span>
                                                    @else
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">নিষ্ক্রিয়</span>
                                                    @endif
                                                </div>
                                                <p class="text-sm text-gray-500 font-mono mt-0.5">
                                                    {{ substr($setting->api_key, 0, 8) }}...{{ substr($setting->api_key, -4) }}
                                                </p>
                                            </div>
                                        </div>

                                        {{-- Actions --}}
                                        <div class="flex items-center space-x-2">
                                            {{-- Test --}}
                                            <a href="{{ route('ai.setup.test', $setting) }}"
                                               class="inline-flex items-center px-3 py-2 text-sm text-gray-600 hover:text-purple-600 hover:bg-purple-50 rounded-lg transition"
                                               title="টেস্ট করুন">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                                </svg>
                                            </a>

                                            {{-- Toggle Active --}}
                                            <form action="{{ route('ai.setup.toggle', $setting) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit"
                                                        class="inline-flex items-center px-3 py-2 text-sm {{ $setting->is_active ? 'text-green-600 hover:text-yellow-600' : 'text-gray-400 hover:text-green-600' }} hover:bg-gray-50 rounded-lg transition"
                                                        title="{{ $setting->is_active ? 'নিষ্ক্রিয় করুন' : 'সক্রিয় করুন' }}">
                                                    @if($setting->is_active)
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                        </svg>
                                                    @else
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                                        </svg>
                                                    @endif
                                                </button>
                                            </form>

                                            {{-- Delete --}}
                                            <form action="{{ route('ai.setup.destroy', $setting) }}" method="POST"
                                                  onsubmit="return confirm('আপনি কি নিশ্চিত এই Key মুছে ফেলতে চান?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex items-center px-3 py-2 text-sm text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                                                        title="মুছে ফেলুন">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4 p-4 bg-blue-50 rounded-xl border border-blue-100">
                            <p class="text-sm text-blue-700">
                                <strong>কিভাবে কাজ করে:</strong> AI সিস্টেম প্রথম Key দিয়ে শুরু করবে। যদি কোনো Key এর দৈনিক লিমিট শেষ হয় (429 error), তাহলে স্বয়ংক্রিয়ভাবে পরবর্তী Key ব্যবহার করবে। সক্রিয় এবং অগ্রাধিকার প্রাপ্ত Key আগে ব্যবহার হবে।
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Status Card --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">স্ট্যাটাস</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">মোট Key</span>
                            <span class="text-sm font-medium text-gray-900">{{ $aiSettings->count() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">সক্রিয়</span>
                            <span class="text-sm font-medium text-green-600">{{ $aiSettings->where('is_active', true)->count() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">নিষ্ক্রিয়</span>
                            <span class="text-sm font-medium text-gray-400">{{ $aiSettings->where('is_active', false)->count() }}</span>
                        </div>
                    </div>
                </div>

                {{-- Instructions --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">কিভাবে পাবেন?</h3>
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="w-6 h-6 bg-purple-100 rounded-full flex items-center justify-center mr-3 mt-0.5">
                                <span class="text-xs font-bold text-purple-600">১</span>
                            </div>
                            <p class="text-sm text-gray-600">Groq ওয়েবসাইটে যান (<a href="https://console.groq.com" target="_blank" class="text-purple-600 hover:underline">console.groq.com</a>)</p>
                        </div>
                        <div class="flex items-start">
                            <div class="w-6 h-6 bg-purple-100 rounded-full flex items-center justify-center mr-3 mt-0.5">
                                <span class="text-xs font-bold text-purple-600">২</span>
                            </div>
                            <p class="text-sm text-gray-600">একাধিক API Key তৈরি করুন (বিভিন্ন Groq অ্যাকাউন্ট থেকে)</p>
                        </div>
                        <div class="flex items-start">
                            <div class="w-6 h-6 bg-purple-100 rounded-full flex items-center justify-center mr-3 mt-0.5">
                                <span class="text-xs font-bold text-purple-600">৩</span>
                            </div>
                            <p class="text-sm text-gray-600">প্রতিটি Key যোগ করুন এবং নাম দিন</p>
                        </div>
                        <div class="flex items-start">
                            <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center mr-3 mt-0.5">
                                <span class="text-xs font-bold text-green-600">৪</span>
                            </div>
                            <p class="text-sm text-gray-600">একটি Key এর লিমিট শেষ হলে অটোমেটিক পরবর্তী Key ব্যবহার হবে</p>
                        </div>
                    </div>
                </div>

                {{-- Info --}}
                <div class="bg-purple-50 rounded-2xl p-6 border border-purple-200">
                    <h3 class="text-lg font-bold text-purple-800 mb-2">তথ্য</h3>
                    <p class="text-sm text-purple-700 mb-3">AI সেটআপ সম্পন্ন হলে আপনার Facebook Messenger এ AI-চালিত অটো রিপ্লাই সক্রিয় হবে।</p>
                    <p class="text-sm text-purple-600"><strong>টিপস:</strong> Groq এর ফ্রি প্লানে দৈনিক লিমিট আছে। একাধিক Key রাখলে একটি লিমিট শেষ হলেও AI কাজ চলতে থাকবে।</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
