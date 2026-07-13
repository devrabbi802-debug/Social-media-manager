@extends('layouts.tenant')

@section('title', 'সেটিংস - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <h1 class="text-2xl font-bold text-gray-900">সেটিংস</h1>
            <p class="text-gray-600">আপনার অ্যাকাউন্ট সেটিংস পরিচালনা করুন</p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @include('tenant.partials._nav-tabs', ['activePage' => 'settings'])

        @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl">
            {{ session('success') }}
        </div>
        @endif

        <div class="grid lg:grid-cols-3 gap-8">
            {{-- Profile Settings --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-6">প্রোফাইল তথ্য</h2>
                    <form method="POST" action="{{ url('/settings/profile') }}">
                        @csrf
                        @method('PUT')

                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">নাম</label>
                                <input type="text" name="name" value="{{ old('name', Auth::user()->name) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                                @error('name')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">ইমেইল</label>
                                <input type="email" name="email" value="{{ old('email', Auth::user()->email) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                                @error('email')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">ফোন</label>
                                <input type="text" name="phone" value="{{ old('phone', Auth::user()->phone) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                                @error('phone')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">কোম্পানি</label>
                                <input type="text" name="company" value="{{ old('company', Auth::user()->company) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                                @error('company')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="bg-purple-600 text-white px-6 py-2.5 rounded-xl font-medium hover:bg-purple-700 transition">
                                সংরক্ষণ করুন
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Password Change --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-6">পাসওয়ার্ড পরিবর্তন</h2>
                    <form method="POST" action="{{ url('/settings/password') }}">
                        @csrf
                        @method('PUT')

                        <div class="space-y-4 max-w-md">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">বর্তমান পাসওয়ার্ড</label>
                                <input type="password" name="current_password" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                                @error('current_password')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">নতুন পাসওয়ার্ড</label>
                                <input type="password" name="password" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                                @error('password')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">পাসওয়ার্ড নিশ্চিত করুন</label>
                                <input type="password" name="password_confirmation" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="bg-purple-600 text-white px-6 py-2.5 rounded-xl font-medium hover:bg-purple-700 transition">
                                পাসওয়ার্ড আপডেট করুন
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Right Column --}}
            <div class="space-y-6">
                {{-- Account Info --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">অ্যাকাউন্ট তথ্য</h2>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-500">ইউজারনেম</span>
                            <span class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</span>
                        </div>
                        <div class="flex items-center justify-between py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-500">ইমেইল</span>
                            <span class="text-sm font-medium text-gray-900">{{ Auth::user()->email }}</span>
                        </div>
                        <div class="flex items-center justify-between py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-500">ইমেইল যাচাই</span>
                            @if(Auth::user()->email_verified_at)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">যাচাইকৃত</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">অযাচাইকৃত</span>
                            @endif
                        </div>
                        <div class="flex items-center justify-between py-2">
                            <span class="text-sm text-gray-500">সদস্য হয়েছেন</span>
                            <span class="text-sm font-medium text-gray-900">{{ Auth::user()->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>

                {{-- Danger Zone --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-red-200">
                    <h2 class="text-lg font-bold text-red-600 mb-4">ঝুঁকিপূর্ণ এলাকা</h2>
                    <p class="text-sm text-gray-600 mb-4">আপনার অ্যাকাউন্ট মুছে ফেললে সব ডাটা মুছে যাবে।</p>
                    <button type="button" class="w-full border border-red-600 text-red-600 px-4 py-2 rounded-xl font-medium hover:bg-red-50 transition">
                        অ্যাকাউন্ট ডিলিট করুন
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
