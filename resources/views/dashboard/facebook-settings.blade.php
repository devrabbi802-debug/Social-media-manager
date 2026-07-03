@extends('layouts.app')

@section('title', 'ফেসবুক সেটিংস - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Dashboard Header --}}
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">ফেসবুক সেটিংস</h1>
                    <p class="text-gray-600">আপনার ফেসবুক অ্যাপ কনফিগারেশন সেট করুন</p>
                </div>
                <div class="flex items-center space-x-4">
                    @if($facebookSetting)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                            সংযুক্ত
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
                <a href="{{ route('facebook.settings') }}" class="whitespace-nowrap py-4 px-1 border-b-2 border-purple-600 text-purple-600 font-medium text-sm transition">
                    ফেসবুক সেটিংস
                </a>
            </nav>
        </div>

        {{-- Success Message --}}
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Main Form --}}
            <div class="lg:col-span-2">
                <form action="{{ route('facebook.settings.store') }}" method="POST">
                    @csrf
                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <div class="flex items-center mb-6">
                            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mr-4">
                                <svg class="w-7 h-7 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388,10.954,10.125,11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007,1.792-4.669,4.533-4.669 1.312,0,2.686.235,2.686.235v2.953H15.83c-1.491,0-1.956.925-1.956,1.874v2.25h3.328l-.532,3.47h-2.796v8.385C19.612,23.027,24,18.062,24,12.073z"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">ফেসবুক অ্যাপ কনফিগারেশন</h2>
                                <p class="text-sm text-gray-500">আপনার Facebook Developer অ্যাকাউন্ট থেকে তথ্য দিন</p>
                            </div>
                        </div>

                        {{-- App ID --}}
                        <div class="mb-5">
                            <label for="app_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                App ID <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="app_id"
                                name="app_id"
                                value="{{ old('app_id', $facebookSetting->app_id ?? '') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('app_id') border-red-500 @enderror"
                                placeholder="যেমন: 123456789012345"
                            >
                            <p class="mt-1 text-xs text-gray-500">Facebook Developer Dashboard থেকে পাবেন</p>
                            @error('app_id')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- App Secret --}}
                        <div class="mb-5">
                            <label for="app_secret" class="block text-sm font-semibold text-gray-700 mb-2">
                                App Secret <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input
                                    type="password"
                                    id="app_secret"
                                    name="app_secret"
                                    value="{{ old('app_secret', $facebookSetting->app_secret ?? '') }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition pr-12 @error('app_secret') border-red-500 @enderror"
                                    placeholder="আপনার App Secret দিন"
                                >
                                <button type="button" onclick="togglePassword('app_secret')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Long-lived token এবং verification-এর জন্য প্রয়োজন</p>
                            @error('app_secret')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Verify Token --}}
                        <div class="mb-5">
                            <label for="verify_token" class="block text-sm font-semibold text-gray-700 mb-2">
                                Verify Token
                            </label>
                            <input
                                type="text"
                                id="verify_token"
                                name="verify_token"
                                value="{{ old('verify_token', $facebookSetting->verify_token ?? 'socialboost_verify_token_2026') }}"
                                readonly
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl bg-gray-50 text-gray-500 cursor-not-allowed"
                            >
                            <p class="mt-1 text-xs text-gray-500">Webhook verify করার জন্য ব্যবহৃত হয়। এটা সিস্টেম জেনারেটেড।</p>
                        </div>

                        {{-- Page ID --}}
                        <div class="mb-5">
                            <label for="page_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                Page ID <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="page_id"
                                name="page_id"
                                value="{{ old('page_id', $facebookSetting->page_id ?? '') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('page_id') border-red-500 @enderror"
                                placeholder="যেমন: 123456789012345"
                            >
                            <p class="mt-1 text-xs text-gray-500">Webhook থেকে আসা event কোন page-এর তা identify করার জন্য</p>
                            @error('page_id')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Page Access Token --}}
                        <div class="mb-6">
                            <label for="page_access_token" class="block text-sm font-semibold text-gray-700 mb-2">
                                Page Access Token <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <textarea
                                    id="page_access_token"
                                    name="page_access_token"
                                    rows="3"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition pr-12 @error('page_access_token') border-red-500 @enderror"
                                    placeholder="আপনার Page Access Token পেস্ট করুন"
                                >{{ old('page_access_token', $facebookSetting->page_access_token ?? '') }}</textarea>
                                <button type="button" onclick="toggleTextarea('page_access_token')" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Message read/send করার জন্য প্রয়োজন</p>
                            @error('page_access_token')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Submit Buttons --}}
                        <div class="flex items-center justify-between">
                            <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-xl font-medium hover:bg-blue-700 transition flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                সংরক্ষণ করুন
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Instructions --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Webhook সেটআপ</h3>
                    <div class="space-y-4">
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Callback URL</p>
                            <code class="text-sm text-blue-600 break-all">{{ url('/webhook/facebook') }}</code>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Verify Token</p>
                            <code class="text-sm text-blue-600">{{ $facebookSetting->verify_token ?? 'socialboost_verify_token_2026' }}</code>
                        </div>
                    </div>
                </div>

                {{-- Instructions --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">কিভাবে পাবেন?</h3>
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mr-3 mt-0.5">
                                <span class="text-xs font-bold text-blue-600">১</span>
                            </div>
                            <p class="text-sm text-gray-600">Facebook Developer Dashboard এ যান (<a href="https://developers.facebook.com" target="_blank" class="text-blue-600 hover:underline">developers.facebook.com</a>)</p>
                        </div>
                        <div class="flex items-start">
                            <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mr-3 mt-0.5">
                                <span class="text-xs font-bold text-blue-600">২</span>
                            </div>
                            <p class="text-sm text-gray-600">আপনার App সিলেক্ট করুন অথবা নতুন App তৈরি করুন</p>
                        </div>
                        <div class="flex items-start">
                            <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mr-3 mt-0.5">
                                <span class="text-xs font-bold text-blue-600">৩</span>
                            </div>
                            <p class="text-sm text-gray-600">Settings > Basic থেকে App ID এবং App Secret কপি করুন</p>
                        </div>
                        <div class="flex items-start">
                            <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mr-3 mt-0.5">
                                <span class="text-xs font-bold text-blue-600">৪</span>
                            </div>
                            <p class="text-sm text-gray-600">Products > Messenger থেকে Page Access Token তৈরি করুন</p>
                        </div>
                        <div class="flex items-start">
                            <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mr-3 mt-0.5">
                                <span class="text-xs font-bold text-blue-600">৫</span>
                            </div>
                            <p class="text-sm text-gray-600">Products > Webhooks এ "Verify Token" ফিল্ডে উপরের টোকেন দিন</p>
                        </div>
                        <div class="flex items-start">
                            <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mr-3 mt-0.5">
                                <span class="text-xs font-bold text-blue-600">৬</span>
                            </div>
                            <p class="text-sm text-gray-600">Callback URL ফিল্ডে উপরের URL দিন এবং Verify ক্লিক করুন</p>
                        </div>
                        <div class="flex items-start">
                            <div class="w-6 h-6 bg-yellow-100 rounded-full flex items-center justify-center mr-3 mt-0.5">
                                <span class="text-xs font-bold text-yellow-600">!</span>
                            </div>
                            <p class="text-sm text-gray-600">Localhost এ থাকলে ngrok বা Cloudflare Tunnel লাগবে। নিচে দেখুন।</p>
                        </div>
                    </div>
                </div>

                {{-- Danger Zone --}}
                @if($facebookSetting)
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-red-200">
                        <h3 class="text-lg font-bold text-red-600 mb-2">বিপদ এলাকা</h3>
                        <p class="text-sm text-gray-600 mb-4">সেটিংস মুছে ফেললে আপনার Facebook ইন্টিগ্রেশন কাজ করবে না।</p>
                        <form action="{{ route('facebook.settings.destroy') }}" method="POST" onsubmit="return confirm('আপনি কি নিশ্চিত এই সেটিংস মুছে ফেলতে চান?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded-xl font-medium hover:bg-red-700 transition">
                                সেটিংস মুছে ফেলুন
                            </button>
                        </form>
                    </div>
                @endif

                {{-- Local Dev Instructions --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-yellow-200">
                    <h3 class="text-lg font-bold text-yellow-600 mb-2">Localhost এ Webhook Test</h3>
                    <p class="text-sm text-gray-600 mb-3">Facebook localhost এ পুরতে পারে না। একটা tunnel লাগবে:</p>
                    <div class="space-y-3">
                        <div class="bg-gray-50 rounded-xl p-3">
                            <p class="text-xs font-semibold text-gray-700 mb-1">Option 1: ngrok</p>
                            <code class="text-xs text-gray-600 block">ngrok http {{ request()->getHost() }}</code>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-3">
                            <p class="text-xs font-semibold text-gray-700 mb-1">Option 2: Cloudflare Tunnel</p>
                            <code class="text-xs text-gray-600 block">cloudflared tunnel --url http://{{ request()->getHost() }}</code>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-3">Tunnel URL কপি করে Facebook Dashboard এ Callback URL হিসেবে দিন।</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    field.type = field.type === 'password' ? 'text' : 'password';
}

function toggleTextarea(fieldId) {
    const field = document.getElementById(fieldId);
    if (field.rows === 3) {
        field.rows = 6;
    } else {
        field.rows = 3;
    }
}
</script>
@endsection
