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
                    <p class="text-gray-600">আপনার AI সেক্রেট কী পরিচালনা করুন</p>
                </div>
                <div class="flex items-center space-x-4">
                    @if($aiSetting)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                            সক্রিয়
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
            {{-- Main Form --}}
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mr-4">
                            <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">AI সেক্রেট কী</h2>
                            <p class="text-sm text-gray-500">Kilo AI API Key দিন</p>
                        </div>
                    </div>

                    <form action="{{ route('ai.setup.store') }}" method="POST">
                        @csrf
                        <div class="mb-6">
                            <label for="api_key" class="block text-sm font-medium text-gray-700 mb-2">সেক্রেট কী</label>
                            <input
                                type="password"
                                id="api_key"
                                name="api_key"
                                value="{{ old('api_key', $aiSetting ? $aiSetting->api_key : '') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition font-mono text-sm"
                                placeholder="sk-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                                required
                            >
                            <p class="mt-2 text-xs text-gray-500">Kilo AI থেকে প্রাপ্ত API Key দিন। এটি শুধুমাত্র আপনার অ্যাকাউন্টে সংরক্ষিত হবে।</p>
                        </div>

                        <div class="flex items-center space-x-4">
                            <button type="submit" class="inline-flex items-center px-6 py-3 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition shadow-lg shadow-purple-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                সংরক্ষণ করুন
                            </button>
                            @if($aiSetting)
                                <a href="{{ route('ai.setup.test') }}" class="inline-flex items-center px-6 py-3 border border-gray-300 rounded-xl text-gray-700 font-medium hover:bg-gray-50 transition">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                    কানেকশন টেস্ট করুন
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Status Card --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">স্ট্যাটাস</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">API Key</span>
                            @if($aiSetting)
                                <span class="text-sm text-green-600 font-medium">সংরক্ষিত</span>
                            @else
                                <span class="text-sm text-yellow-600 font-medium">সেট করা হয়নি</span>
                            @endif
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
                            <p class="text-sm text-gray-600">Kilo AI ওয়েবসাইটে যান (<a href="https://kilo.ai" target="_blank" class="text-purple-600 hover:underline">kilo.ai</a>)</p>
                        </div>
                        <div class="flex items-start">
                            <div class="w-6 h-6 bg-purple-100 rounded-full flex items-center justify-center mr-3 mt-0.5">
                                <span class="text-xs font-bold text-purple-600">২</span>
                            </div>
                            <p class="text-sm text-gray-600">আপনার অ্যাকাউন্ট থেকে API Key তৈরি বা কপি করুন</p>
                        </div>
                        <div class="flex items-start">
                            <div class="w-6 h-6 bg-purple-100 rounded-full flex items-center justify-center mr-3 mt-0.5">
                                <span class="text-xs font-bold text-purple-600">৩</span>
                            </div>
                            <p class="text-sm text-gray-600">উপরের ফিল্ডে Key পেস্ট করুন এবং সংরক্ষণ করুন</p>
                        </div>
                        <div class="flex items-start">
                            <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center mr-3 mt-0.5">
                                <span class="text-xs font-bold text-green-600">৪</span>
                            </div>
                            <p class="text-sm text-gray-600">কানেকশন টেস্ট করে দেখুন সব কি ঠিকমতো কাজ করছে</p>
                        </div>
                    </div>
                </div>

                {{-- Info --}}
                <div class="bg-purple-50 rounded-2xl p-6 border border-purple-200">
                    <h3 class="text-lg font-bold text-purple-800 mb-2">তথ্য</h3>
                    <p class="text-sm text-purple-700">AI সেটআপ সম্পন্ন হলে আপনার Facebook Messenger এ AI-চালিত অটো রিপ্লাই সক্রিয় হবে।</p>
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
</script>
@endsection
