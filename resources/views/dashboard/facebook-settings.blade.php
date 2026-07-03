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
                @if($facebookSetting)
                    {{-- Connected State --}}
                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <div class="flex items-center mb-6">
                            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mr-4">
                                <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">ফেসবুক সংযুক্ত আছে</h2>
                                <p class="text-sm text-gray-500">আপনার Facebook Page সফলভাবে সংযুক্ত হয়েছে</p>
                            </div>
                        </div>

                        <div class="bg-gray-50 rounded-xl p-4 mb-6">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase">Page ID</p>
                                    <p class="text-sm text-gray-900 mt-1">{{ $facebookSetting->page_id }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase">App ID</p>
                                    <p class="text-sm text-gray-900 mt-1">{{ $facebookSetting->app_id }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase">Verify Token</p>
                                    <p class="text-sm text-gray-900 mt-1 font-mono">{{ $facebookSetting->verify_token }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase">Status</p>
                                    <p class="text-sm text-green-600 mt-1 font-medium">সংযুক্ত</p>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('facebook.redirect') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388,10.954,10.125,11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007,1.792-4.669,4.533-4.669 1.312,0,2.686.235,2.686.235v2.953H15.83c-1.491,0-1.956.925-1.956,1.874v2.25h3.328l-.532,3.47h-2.796v8.385C19.612,23.027,24,18.062,24,12.073z"/>
                            </svg>
                            Page পরিবর্তন করুন
                        </a>
                    </div>
                @else
                    {{-- Not Connected State --}}
                    <div class="bg-white rounded-2xl p-8 shadow-sm text-center">
                        <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                            <svg class="w-10 h-10 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388,10.954,10.125,11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007,1.792-4.669,4.533-4.669 1.312,0,2.686.235,2.686.235v2.953H15.83c-1.491,0-1.956.925-1.956,1.874v2.25h3.328l-.532,3.47h-2.796v8.385C19.612,23.027,24,18.062,24,12.073z"/>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 mb-2">Facebook Page সংযুক্ত করুন</h2>
                        <p class="text-gray-500 mb-6">একটি ক্লিকে আপনার Facebook Page সংযুক্ত হয়ে যাবে</p>

                        <a href="{{ route('facebook.redirect') }}" class="inline-flex items-center px-8 py-4 bg-blue-600 text-white rounded-xl font-bold text-lg hover:bg-blue-700 transition shadow-lg shadow-blue-200">
                            <svg class="w-6 h-6 mr-3" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388,10.954,10.125,11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007,1.792-4.669,4.533-4.669 1.312,0,2.686.235,2.686.235v2.953H15.83c-1.491,0-1.956.925-1.956,1.874v2.25h3.328l-.532,3.47h-2.796v8.385C19.612,23.027,24,18.062,24,12.073z"/>
                            </svg>
                            Facebook দিয়ে সংযুক্ত করুন
                        </a>

                        <p class="text-xs text-gray-400 mt-4">Facebook Login ব্যবহার করে নিরাপদভাবে সংযুক্ত হবে। আপনার পাসওয়ার্ড আমাদের কাছে সংরক্ষিত হবে না।</p>
                    </div>
                @endif
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
