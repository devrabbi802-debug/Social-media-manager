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
                    <p class="text-gray-600">Message AI এবং Image AI কী পরিচালনা করুন</p>
                </div>
                <div class="flex items-center space-x-4">
                    @php
                        $totalActive = $messageKeys->where('is_active', true)->count() + $imageKeys->where('is_active', true)->count();
                    @endphp
                    @if($totalActive > 0)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                            {{ $totalActive }} সক্রিয়
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
        @include('dashboard.partials._nav-tabs', ['activePage' => 'ai.setup'])

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

        {{-- Tabs --}}
        <div class="mb-8">
            <div class="border-b border-gray-200">
                <nav class="flex space-x-8">
                    <a href="{{ route('ai.setup', ['tab' => 'message']) }}"
                       class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition {{ $activeTab === 'message' ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            Message AI
                            @if($messageKeys->where('is_active', true)->count() > 0)
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                    {{ $messageKeys->where('is_active', true)->count() }}
                                </span>
                            @endif
                        </span>
                    </a>
                    <a href="{{ route('ai.setup', ['tab' => 'image']) }}"
                       class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition {{ $activeTab === 'image' ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Image AI (Gemini)
                            @if($imageKeys->where('is_active', true)->count() > 0)
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                    {{ $imageKeys->where('is_active', true)->count() }}
                                </span>
                            @endif
                        </span>
                    </a>
                </nav>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-6">
                @if($activeTab === 'message')
                    {{-- Message AI --}}
                    @include('dashboard.partials._message-ai-tab', ['messageKeys' => $messageKeys])
                @else
                    {{-- Image AI --}}
                    @include('dashboard.partials._image-ai-tab', ['imageKeys' => $imageKeys])
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Status Card --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">স্ট্যাটাস</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">Message AI</span>
                            @if($messageKeys->where('is_active', true)->isNotEmpty())
                                <span class="text-sm font-medium text-green-600">{{ $messageKeys->where('is_active', true)->count() }} সক্রিয়</span>
                            @else
                                <span class="text-sm font-medium text-yellow-600">সেট করা হয়নি</span>
                            @endif
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">Image AI</span>
                            @if($imageKeys->where('is_active', true)->isNotEmpty())
                                <span class="text-sm font-medium text-green-600">{{ $imageKeys->where('is_active', true)->count() }} সক্রিয়</span>
                            @else
                                <span class="text-sm font-medium text-yellow-600">সেট করা হয়নি</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Instructions --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">কিভাবে কাজ করে?</h3>
                    <div class="space-y-4">
                        @if($activeTab === 'message')
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
                                <p class="text-sm text-gray-600">একাধিক API Key তৈরি করুন</p>
                            </div>
                            <div class="flex items-start">
                                <div class="w-6 h-6 bg-purple-100 rounded-full flex items-center justify-center mr-3 mt-0.5">
                                    <span class="text-xs font-bold text-purple-600">৩</span>
                                </div>
                                <p class="text-sm text-gray-600">একটি Key এর লিমিট শেষ হলে অটোমেটিক পরবর্তী Key ব্যবহার হবে</p>
                            </div>
                        @else
                            <div class="flex items-start">
                                <div class="w-6 h-6 bg-purple-100 rounded-full flex items-center justify-center mr-3 mt-0.5">
                                    <span class="text-xs font-bold text-purple-600">১</span>
                                </div>
                                <p class="text-sm text-gray-600">Google AI Studio এ যান (<a href="https://aistudio.google.com/apikey" target="_blank" class="text-purple-600 hover:underline">aistudio.google.com</a>)</p>
                            </div>
                            <div class="flex items-start">
                                <div class="w-6 h-6 bg-purple-100 rounded-full flex items-center justify-center mr-3 mt-0.5">
                                    <span class="text-xs font-bold text-purple-600">২</span>
                                </div>
                                <p class="text-sm text-gray-600">Gemini API Key তৈরি করুন</p>
                            </div>
                            <div class="flex items-start">
                                <div class="w-6 h-6 bg-purple-100 rounded-full flex items-center justify-center mr-3 mt-0.5">
                                    <span class="text-xs font-bold text-purple-600">৩</span>
                                </div>
                                <p class="text-sm text-gray-600">Image AI তে Key যোগ করুন — Facebook Messenger এ ইমেজ পাঠাতে পারবেন</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Info --}}
                <div class="bg-purple-50 rounded-2xl p-6 border border-purple-200">
                    <h3 class="text-lg font-bold text-purple-800 mb-2">তথ্য</h3>
                    @if($activeTab === 'message')
                        <p class="text-sm text-purple-700 mb-3">Message AI ব্যবহার করে Facebook Messenger এ অটো রিপ্লাই দেবে।</p>
                        <p class="text-sm text-purple-600"><strong>টিপস:</strong> Groq এর ফ্রি প্লানে দৈনিক লিমিট আছে। একাধিক Key রাখলে একটি লিমিট শেষ হলেও AI কাজ চলতে থাকবে।</p>
                    @else
                        <p class="text-sm text-purple-700 mb-3">Image AI ব্যবহার করে Facebook Messenger এ ইমেজ তৈরি ও পাঠাতে পারবেন।</p>
                        <p class="text-sm text-purple-600"><strong>টিপস:</strong> Gemini API ব্যবহার করে ইমেজ তৈরি করবে। Google AI Studio থেকে ফ্রি API Key পাওয়া যায়।</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
