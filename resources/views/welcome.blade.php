@extends('layouts.app')

@section('title', 'SocialBoost AI - সোশ্যাল মিডিয়া ম্যানেজমেন্ট প্ল্যাটফর্ম')

@section('content')
{{-- Hero Section --}}
<section class="relative overflow-hidden bg-gradient-to-br from-purple-50 via-white to-blue-50 py-20 lg:py-32">
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-purple-200 rounded-full mix-blend-multiply filter blur-3xl opacity-50 float-animation"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-blue-200 rounded-full mix-blend-multiply filter blur-3xl opacity-50 float-animation" style="animation-delay: 2s;"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <div class="inline-flex items-center bg-purple-100 text-purple-700 px-4 py-2 rounded-full text-sm font-medium mb-6">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    AI-চালিত সমাধান
                </div>
                <h1 class="text-4xl lg:text-6xl font-bold text-gray-900 mb-6 leading-tight">
                    আপনার সোশ্যাল মিডিয়া
                    <span class="gradient-text">স্মার্টভাবে পরিচালনা</span>
                    করুন
                </h1>
                <p class="text-xl text-gray-600 mb-8 leading-relaxed">
                    WhatsApp এবং Facebook-এ AI-চালিত অটো-রিপ্লাই দিয়ে কাস্টমার মেসেজ ম্যানেজ করুন।
                    লিড সংগ্রহ করুন, অর্ডার ট্র্যাক করুন এবং ইনভেন্টরি পরিচালনা করুন।
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="{{ url('/register') }}" class="gradient-bg text-white px-8 py-4 rounded-full font-semibold text-lg hover:opacity-90 transition pulse-glow text-center">
                        বিনামূল্যে শুরু করুন
                    </a>
                    <a href="#demo" class="border-2 border-purple-600 text-purple-600 px-8 py-4 rounded-full font-semibold text-lg hover:bg-purple-50 transition text-center">
                        ডেমো দেখুন
                    </a>
                </div>
                <div class="mt-8 flex items-center space-x-6 text-sm text-gray-500">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        ক্রেডিট কার্ড প্রয়োজন নেই
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        ১৪ দিনের ফ্রি ট্রায়াল
                    </div>
                </div>
            </div>
            <div class="relative">
                <div class="bg-white rounded-2xl shadow-2xl p-6 float-animation">
                    <div class="bg-gradient-to-r from-purple-500 to-blue-500 rounded-xl p-4 text-white mb-4">
                        <div class="flex items-center space-x-3 mb-3">
                            <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.832-1.438A9.955 9.955 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold">WhatsApp</p>
                                <p class="text-xs text-white/70">অটো-রিপ্লাই সক্রিয়</p>
                            </div>
                        </div>
                        <div class="bg-white/10 rounded-lg p-3">
                            <p class="text-sm">"আমাদের পণ্যের দাম কত?"</p>
                            <p class="text-xs text-white/70 mt-2">AI উত্তর: "আমাদের পণ্য ৫০০ টাকা থেকে শুরু..."</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">আজকের লিড:</span>
                        <span class="font-bold text-purple-600">২৪টি</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Features Section --}}
<section id="features" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">শক্তিশালী ফিচারসমূহ</h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">আপনার ব্যবসার জন্য প্রয়োজনীয় সব কিছু একটি প্ল্যাটফর্মে</p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            {{-- Feature 1 --}}
            <div class="bg-gradient-to-br from-purple-50 to-blue-50 rounded-2xl p-8 card-hover">
                <div class="w-14 h-14 gradient-bg rounded-xl flex items-center justify-center mb-6">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">AI অটো-রিপ্লাই</h3>
                <p class="text-gray-600">WhatsApp এবং Facebook-এ AI-চালিত অটো-রিপ্লাই দিয়ে কাস্টমার সেবা ২৪/৭ প্রদান করুন।</p>
            </div>

            {{-- Feature 2 --}}
            <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-2xl p-8 card-hover">
                <div class="w-14 h-14 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center mb-6">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">লিড ম্যানেজমেন্ট</h3>
                <p class="text-gray-600">কাস্টমার অর্ডার এবং মেসেজ অটোমেটিক্যালি লিড হিসেবে সংগ্রহ ও স্টোর করুন।</p>
            </div>

            {{-- Feature 3 --}}
            <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl p-8 card-hover">
                <div class="w-14 h-14 bg-gradient-to-r from-green-500 to-emerald-500 rounded-xl flex items-center justify-center mb-6">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">ইনভেন্টরি ম্যানেজমেন্ট</h3>
                <p class="text-gray-600">পণ্যের স্টক ট্র্যাক করুন, অটোমেটিক রিস্টক অ্যালার্ট পান এবং সেলস রিপোর্ট দেখুন।</p>
            </div>

            {{-- Feature 4 --}}
            <div class="bg-gradient-to-br from-orange-50 to-red-50 rounded-2xl p-8 card-hover">
                <div class="w-14 h-14 bg-gradient-to-r from-orange-500 to-red-500 rounded-xl flex items-center justify-center mb-6">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">অ্যানালিটিক্স ড্যাশবোর্ড</h3>
                <p class="text-gray-600">বিস্তারিত রিপোর্ট এবং অ্যানালিটিক্স দিয়ে আপনার ব্যবসার পারফরম্যান্স মাপুন।</p>
            </div>

            {{-- Feature 5 --}}
            <div class="bg-gradient-to-br from-pink-50 to-rose-50 rounded-2xl p-8 card-hover">
                <div class="w-14 h-14 bg-gradient-to-r from-pink-500 to-rose-500 rounded-xl flex items-center justify-center mb-6">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">সিঙ্ক ও স্টোর</h3>
                <p class="text-gray-600">Facebook এবং WhatsApp থেকে অর্ডার অটোমেটিক্যালি সিঙ্ক হয়ে ব্যাকএন্ডে স্টোর হয়।</p>
            </div>

            {{-- Feature 6 --}}
            <div class="bg-gradient-to-br from-indigo-50 to-violet-50 rounded-2xl p-8 card-hover">
                <div class="w-14 h-14 bg-gradient-to-r from-indigo-500 to-violet-500 rounded-xl flex items-center justify-center mb-6">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">নিরাপদ প্ল্যাটফর্ম</h3>
                <p class="text-gray-600">এনক্রিপ্টেড ডেটা স্টোরেজ এবং উন্নত নিরাপত্তা বৈশিষ্ট্য দিয়ে আপনার তথ্য সুরক্ষিত।</p>
            </div>
        </div>
    </div>
</section>

{{-- How It Works Section --}}
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">কিভাবে কাজ করে?</h2>
            <p class="text-xl text-gray-600">মাত্র ৩টি সহজ ধাপে আপনার ব্যবসা শুরু করুন</p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            <div class="text-center">
                <div class="w-20 h-20 gradient-bg rounded-full flex items-center justify-center mx-auto mb-6 text-white text-3xl font-bold">১</div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">অ্যাকাউন্ট তৈরি করুন</h3>
                <p class="text-gray-600">আপনার ব্যবসার তথ্য দিয়ে একটি ফ্রি অ্যাকাউন্ট তৈরি করুন।</p>
            </div>
            <div class="text-center">
                <div class="w-20 h-20 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-full flex items-center justify-center mx-auto mb-6 text-white text-3xl font-bold">২</div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">প্ল্যাটফর্ম সংযুক্ত করুন</h3>
                <p class="text-gray-600">WhatsApp এবং Facebook অ্যাকাউন্ট সংযুক্ত করুন।</p>
            </div>
            <div class="text-center">
                <div class="w-20 h-20 bg-gradient-to-r from-green-500 to-emerald-500 rounded-full flex items-center justify-center mx-auto mb-6 text-white text-3xl font-bold">৩</div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">AI চালু করুন</h3>
                <p class="text-gray-600">AI অটো-রিপ্লাই চালু করুন এবং কাস্টমার সেবা শুরু করুন।</p>
            </div>
        </div>
    </div>
</section>

{{-- Pricing Section --}}
<section id="pricing" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">মূল্য পরিকল্পনা</h2>
            <p class="text-xl text-gray-600">আপনার ব্যবসার জন্য সঠিক প্ল্যান বেছে নিন</p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            {{-- Starter Plan --}}
            <div class="bg-white border-2 border-gray-200 rounded-2xl p-8 card-hover">
                <div class="text-center mb-8">
                    <h3 class="text-xl font-bold text-gray-900 mb-2">স্টার্টার</h3>
                    <div class="text-4xl font-bold text-gray-900 mb-2">১,৪৯৯<span class="text-lg text-gray-500">$/মাস</span></div>
                    <p class="text-gray-500">ছোট ব্যবসার জন্য আদর্শ</p>
                </div>
                <ul class="space-y-4 mb-8">
                    <li class="flex items-center text-gray-600">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        ১টি WhatsApp নম্বর
                    </li>
                    <li class="flex items-center text-gray-600">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        ১টি Facebook পেজ
                    </li>
                    <li class="flex items-center text-gray-600">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        ৫০০টি AI রিপ্লাই/মাস
                    </li>
                    <li class="flex items-center text-gray-600">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        বেসিক ইনভেন্টরি
                    </li>
                </ul>
                <a href="{{ url('/register') }}" class="block w-full text-center border-2 border-purple-600 text-purple-600 px-6 py-3 rounded-full font-semibold hover:bg-purple-50 transition">
                    শুরু করুন
                </a>
            </div>

            {{-- Professional Plan --}}
            <div class="bg-gradient-to-br from-purple-600 to-blue-600 rounded-2xl p-8 card-hover text-white relative">
                <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                    <span class="bg-yellow-400 text-yellow-900 px-4 py-1 rounded-full text-sm font-bold">জনপ্রিয়</span>
                </div>
                <div class="text-center mb-8">
                    <h3 class="text-xl font-bold mb-2">প্রফেশনাল</h3>
                    <div class="text-4xl font-bold mb-2">৩,৪৯৯<span class="text-lg text-white/70">$/মাস</span></div>
                    <p class="text-white/70">মাঝারি ব্যবসার জন্য আদর্শ</p>
                </div>
                <ul class="space-y-4 mb-8">
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-green-300 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        ৩টি WhatsApp নম্বর
                    </li>
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-green-300 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        ৫টি Facebook পেজ
                    </li>
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-green-300 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        ২,০০০টি AI রিপ্লাই/মাস
                    </li>
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-green-300 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        অ্যাডভান্সড ইনভেন্টরি
                    </li>
                    <li class="flex items-center">
                        <svg class="w-5 h-5 text-green-300 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        অ্যানালিটিক্স ড্যাশবোর্ড
                    </li>
                </ul>
                <a href="{{ url('/register') }}" class="block w-full text-center bg-white text-purple-600 px-6 py-3 rounded-full font-semibold hover:bg-gray-100 transition">
                    শুরু করুন
                </a>
            </div>

            {{-- Enterprise Plan --}}
            <div class="bg-white border-2 border-gray-200 rounded-2xl p-8 card-hover">
                <div class="text-center mb-8">
                    <h3 class="text-xl font-bold text-gray-900 mb-2">এন্টারপ্রাইজ</h3>
                    <div class="text-4xl font-bold text-gray-900 mb-2">৭,৯৯৯<span class="text-lg text-gray-500">$/মাস</span></div>
                    <p class="text-gray-500">বড় ব্যবসার জন্য আদর্শ</p>
                </div>
                <ul class="space-y-4 mb-8">
                    <li class="flex items-center text-gray-600">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        অসীমিত WhatsApp নম্বর
                    </li>
                    <li class="flex items-center text-gray-600">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        অসীমিত Facebook পেজ
                    </li>
                    <li class="flex items-center text-gray-600">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        অসীমিত AI রিপ্লাই
                    </li>
                    <li class="flex items-center text-gray-600">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        কাস্টম AI ট্রেনিং
                    </li>
                    <li class="flex items-center text-gray-600">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        ডেডিকেটেড সাপোর্ট
                    </li>
                </ul>
                <a href="{{ url('/contact') }}" class="block w-full text-center border-2 border-purple-600 text-purple-600 px-6 py-3 rounded-full font-semibold hover:bg-purple-50 transition">
                    যোগাযোগ করুন
                </a>
            </div>
        </div>
    </div>
</section>

{{-- Testimonials Section --}}
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">কাস্টমারদের মতামত</h2>
            <p class="text-xl text-gray-600">আমাদের কাস্টমাররা কী বলছেন</p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            <div class="bg-white rounded-2xl p-8 shadow-sm card-hover">
                <div class="flex items-center mb-4">
                    <div class="flex text-yellow-400">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    </div>
                </div>
                <p class="text-gray-600 mb-4">"SocialBoost AI আমাদের কাস্টমার সার্ভিসকে সম্পূর্ণ বদলে দিয়েছে। AI অটো-রিপ্লাই দিয়ে আমরা এখন ২৪/৭ সেবা দিতে পারছি।"</p>
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center text-purple-600 font-bold">র</div>
                    <div class="ml-3">
                        <p class="font-semibold text-gray-900">রাকিব হাসান</p>
                        <p class="text-sm text-gray-500">ফ্যাশন হাউস</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-8 shadow-sm card-hover">
                <div class="flex items-center mb-4">
                    <div class="flex text-yellow-400">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    </div>
                </div>
                <p class="text-gray-600 mb-4">"ইনভেন্টরি ম্যানেজমেন্ট ফিচারটি অসাধারণ। এখন আমি স্টক ট্র্যাক করতে পারছি এবং অটোমেটিক রিস্টক অ্যালার্ট পাচ্ছি।"</p>
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 font-bold">স</div>
                    <div class="ml-3">
                        <p class="font-semibold text-gray-900">সাবরিনা আক্তার</p>
                        <p class="text-sm text-gray-500">ই-কমার্স বিজনেস</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-8 shadow-sm card-hover">
                <div class="flex items-center mb-4">
                    <div class="flex text-yellow-400">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    </div>
                </div>
                <p class="text-gray-600 mb-4">"লিড ম্যানেজমেন্ট সিস্টেমটি আমার সেলস টিমকে অনেক বেশি কার্যকর করেছে। এখন কোনো লিড মিস হয় না।"</p>
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center text-green-600 font-bold">ক</div>
                    <div class="ml-3">
                        <p class="font-semibold text-gray-900">কামরুজ্জামান</p>
                        <p class="text-sm text-gray-500">রিয়েল এস্টেট</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- CTA Section --}}
<section class="py-20 gradient-bg">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl lg:text-4xl font-bold text-white mb-6">আজই শুরু করুন</h2>
        <p class="text-xl text-white/80 mb-8">১৪ দিনের বিনামূল্যে ট্রায়ালে আপনার ব্যবসা রূপান্তরিত করুন</p>
        <a href="{{ url('/register') }}" class="inline-block bg-white text-purple-600 px-8 py-4 rounded-full font-semibold text-lg hover:bg-gray-100 transition">
            বিনামূল্যে শুরু করুন
        </a>
    </div>
</section>
@endsection
