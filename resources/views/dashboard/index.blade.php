@extends('layouts.app')

@section('title', 'ড্যাশবোর্ড - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Dashboard Header --}}
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">ড্যাশবোর্ড</h1>
                    <p class="text-gray-600">স্বাগতম, {{ Auth::user()->name ?? 'ব্যবহারকারী' }}</p>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                        সক্রিয়
                    </span>
                    <a href="{{ url('/settings') }}" class="text-gray-600 hover:text-purple-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
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
                <a href="{{ route('dashboard') }}" class="whitespace-nowrap py-4 px-1 border-b-2 border-purple-600 text-purple-600 font-medium text-sm transition">
                    ড্যাশবোর্ড
                </a>
                <a href="{{ route('integration') }}" class="whitespace-nowrap py-4 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium text-sm transition">
                    সোশ্যাল মিডিয়া ইন্টিগ্রেশন
                </a>
                <a href="{{ route('ai.setup') }}" class="whitespace-nowrap py-4 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium text-sm transition">
                    AI সেটআপ
                </a>
            </nav>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-2xl p-6 shadow-sm card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">মোট লিড</p>
                        <p class="text-3xl font-bold text-gray-900">১২৪</p>
                        <p class="text-sm text-green-600 mt-1">+১২% গত মাস থেকে</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">AI রিপ্লাই</p>
                        <p class="text-3xl font-bold text-gray-900">৪৫৬</p>
                        <p class="text-sm text-green-600 mt-1">+২৫% গত মাস থেকে</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">অর্ডার</p>
                        <p class="text-3xl font-bold text-gray-900">৮৯</p>
                        <p class="text-sm text-green-600 mt-1">+১৮% গত মাস থেকে</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">ইনভেন্টরি</p>
                        <p class="text-3xl font-bold text-gray-900">৩২</p>
                        <p class="text-sm text-orange-600 mt-1">৫টি স্টক কম</p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
            {{-- Left Column --}}
            <div class="lg:col-span-2 space-y-8">
                {{-- WhatsApp & Facebook Integration --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">প্ল্যাটফর্ম ইন্টিগ্রেশন</h2>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="border border-gray-200 rounded-xl p-4">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                        <svg class="w-6 h-6 text-green-600" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.832-1.438A9.955 9.955 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900">WhatsApp</h3>
                                        <p class="text-sm text-green-600">সংযুক্ত</p>
                                    </div>
                                </div>
                                <span class="w-3 h-3 bg-green-500 rounded-full"></span>
                            </div>
                            <p class="text-sm text-gray-500">নম্বর: +880 1XXXXXXXXX</p>
                            <p class="text-sm text-gray-500">আজকের মেসেজ: ২৪টি</p>
                        </div>

                        <div class="border border-gray-200 rounded-xl p-4">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                        <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388,10.954,10.125,11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007,1.792-4.669,4.533-4.669 1.312,0,2.686.235,2.686.235v2.953H15.83c-1.491,0-1.956.925-1.956,1.874v2.25h3.328l-.532,3.47h-2.796v8.385C19.612,23.027,24,18.062,24,12.073z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900">Facebook</h3>
                                        <p class="text-sm text-green-600">সংযুক্ত</p>
                                    </div>
                                </div>
                                <span class="w-3 h-3 bg-green-500 rounded-full"></span>
                            </div>
                            <p class="text-sm text-gray-500">পেজ: My Business Page</p>
                            <p class="text-sm text-gray-500">আজকের মেসেজ: ১৮টি</p>
                        </div>
                    </div>
                </div>

                {{-- Recent Leads --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-gray-900">সাম্প্রতিক লিড</h2>
                        <a href="{{ url('/leads') }}" class="text-purple-600 hover:text-purple-700 text-sm font-medium">সব দেখুন</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="text-left text-sm text-gray-500 border-b">
                                    <th class="pb-3 font-medium">নাম</th>
                                    <th class="pb-3 font-medium">প্ল্যাটফর্ম</th>
                                    <th class="pb-3 font-medium">মেসেজ</th>
                                    <th class="pb-3 font-medium">সময়</th>
                                    <th class="pb-3 font-medium">স্ট্যাটাস</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr>
                                    <td class="py-3">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center text-purple-600 font-bold text-sm">র</div>
                                            <span class="ml-3 font-medium text-gray-900">রাকিব হাসান</span>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">WhatsApp</span>
                                    </td>
                                    <td class="py-3 text-gray-600 text-sm">"পণ্যের দাম কত?"</td>
                                    <td class="py-3 text-gray-500 text-sm">২ মিনিট আগে</td>
                                    <td class="py-3">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">নতুন</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="py-3">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 font-bold text-sm">স</div>
                                            <span class="ml-3 font-medium text-gray-900">সাবরিনা আক্তার</span>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Facebook</span>
                                    </td>
                                    <td class="py-3 text-gray-600 text-sm">"অর্ডার কিভাবে করব?"</td>
                                    <td class="py-3 text-gray-500 text-sm">৫ মিনিট আগে</td>
                                    <td class="py-3">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">সম্পন্ন</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="py-3">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center text-green-600 font-bold text-sm">ক</div>
                                            <span class="ml-3 font-medium text-gray-900">কামরুজ্জামান</span>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">WhatsApp</span>
                                    </td>
                                    <td class="py-3 text-gray-600 text-sm">"ডেলিভারি চার্জ কত?"</td>
                                    <td class="py-3 text-gray-500 text-sm">১০ মিনিট আগে</td>
                                    <td class="py-3">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">চলমান</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Inventory Status --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-gray-900">ইনভেন্টরি স্ট্যাটাস</h2>
                        <a href="{{ url('/inventory') }}" class="text-purple-600 hover:text-purple-700 text-sm font-medium">সব দেখুন</a>
                    </div>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">ফ্যাশন প্রোডাক্ট A</p>
                                    <p class="text-sm text-gray-500">স্টক: ৪৫ পিস</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">ভালো</span>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">ইলেকট্রনিকস প্রোডাক্ট B</p>
                                    <p class="text-sm text-gray-500">স্টক: ৮ পিস</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">কম</span>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">হোম ডেকোর C</p>
                                    <p class="text-sm text-gray-500">স্টক: ২ পিস</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">সমস্যা</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column --}}
            <div class="space-y-8">
                {{-- Subscription Status --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">সাবস্ক্রিপশন</h2>
                    <div class="bg-gradient-to-br from-purple-600 to-blue-600 rounded-xl p-4 text-white">
                        <div class="flex items-center justify-between mb-3">
                            <span class="font-semibold">প্রফেশনাল প্ল্যান</span>
                            <span class="text-sm text-white/70">মাসিক</span>
                        </div>
                        <div class="mb-3">
                            <div class="flex justify-between text-sm mb-1">
                                <span>AI রিপ্লাই ব্যবহার</span>
                                <span>৪৫৬/২,০০০</span>
                            </div>
                            <div class="w-full bg-white/20 rounded-full h-2">
                                <div class="bg-white rounded-full h-2" style="width: 22.8%"></div>
                            </div>
                        </div>
                        <p class="text-sm text-white/70">বিলিং সাইকেল: ১-৩০ জুন, ২০২৬</p>
                    </div>
                    <a href="{{ url('/pricing') }}" class="block w-full text-center mt-4 border border-purple-600 text-purple-600 px-4 py-2 rounded-xl font-medium hover:bg-purple-50 transition">
                        প্ল্যান আপগ্রেড করুন
                    </a>
                </div>

                {{-- Quick Actions --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">দ্রুত কাজ</h2>
                    <div class="space-y-3">
                        <a href="{{ url('/whatsapp/send') }}" class="flex items-center p-3 bg-green-50 rounded-xl hover:bg-green-100 transition">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-green-600" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.832-1.438A9.955 9.955 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">WhatsApp মেসেজ পাঠান</p>
                                <p class="text-sm text-gray-500">কাস্টমারকে মেসেজ পাঠান</p>
                            </div>
                        </a>

                        <a href="{{ url('/facebook/post') }}" class="flex items-center p-3 bg-blue-50 rounded-xl hover:bg-blue-100 transition">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388,10.954,10.125,11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007,1.792-4.669,4.533-4.669 1.312,0,2.686.235,2.686.235v2.953H15.83c-1.491,0-1.956.925-1.956,1.874v2.25h3.328l-.532,3.47h-2.796v8.385C19.612,23.027,24,18.062,24,12.073z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">Facebook পোস্ট করুন</p>
                                <p class="text-sm text-gray-500">নতুন পোস্ট তৈরি করুন</p>
                            </div>
                        </a>

                        <a href="{{ url('/inventory/add') }}" class="flex items-center p-3 bg-orange-50 rounded-xl hover:bg-orange-100 transition">
                            <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">পণ্য যোগ করুন</p>
                                <p class="text-sm text-gray-500">ইনভেন্টরিতে নতুন পণ্য যোগ করুন</p>
                            </div>
                        </a>

                        <a href="{{ url('/reports') }}" class="flex items-center p-3 bg-purple-50 rounded-xl hover:bg-purple-100 transition">
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">রিপোর্ট দেখুন</p>
                                <p class="text-sm text-gray-500">বিস্তারিত অ্যানালিটিক্স</p>
                            </div>
                        </a>
                    </div>
                </div>

                {{-- Recent Activity --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">সাম্প্রতিক কার্যক্রম</h2>
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-900">AI রিপ্লাই পাঠানো হয়েছে</p>
                                <p class="text-xs text-gray-500">রাকিব হাসানকে WhatsApp-এ</p>
                                <p class="text-xs text-gray-400">২ মিনিট আগে</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-900">নতুন অর্ডার এসেছে</p>
                                <p class="text-xs text-gray-500">সাবরিনা আক্তার - ৫০০ টাকা</p>
                                <p class="text-xs text-gray-400">৫ মিনিট আগে</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-900">স্টক কমে গেছে</p>
                                <p class="text-xs text-gray-500">হোম ডেকোর C - মাত্র ২ পিস বাকি</p>
                                <p class="text-xs text-gray-400">১ ঘণ্টা আগে</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
