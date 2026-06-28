@extends('layouts.app')

@section('title', 'ফিচার - SocialBoost AI')

@section('content')
{{-- Hero Section --}}
<section class="py-20 bg-gradient-to-br from-purple-50 via-white to-blue-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-6">শক্তিশালী ফিচারসমূহ</h1>
        <p class="text-xl text-gray-600 max-w-2xl mx-auto">আপনার ব্যবসার জন্য প্রয়োজনীয় সব কিছু একটি প্ল্যাটফর্মে</p>
    </div>
</section>

{{-- Features Detail --}}
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Feature 1 --}}
        <div class="grid lg:grid-cols-2 gap-12 items-center mb-20">
            <div>
                <div class="inline-flex items-center bg-purple-100 text-purple-700 px-4 py-2 rounded-full text-sm font-medium mb-4">
                    WhatsApp ও Facebook
                </div>
                <h2 class="text-3xl font-bold text-gray-900 mb-4">AI অটো-রিপ্লাই</h2>
                <p class="text-gray-600 mb-6">
                    WhatsApp এবং Facebook-এ AI-চালিত অটো-রিপ্লাই দিয়ে কাস্টমার সেবা ২৪/৭ প্রদান করুন।
                    আমাদের AI সিস্টেম স্বয়ংক্রিয়ভাবে কাস্টমারদের প্রশ্নের উত্তর দেয় এবং তাদের সাহায্য করে।
                </p>
                <ul class="space-y-3">
                    <li class="flex items-center text-gray-600">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        স্বয়ংক্রিয় উত্তর
                    </li>
                    <li class="flex items-center text-gray-600">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        ২৪/৭ সেবা
                    </li>
                    <li class="flex items-center text-gray-600">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        বাংলা ভাষা সাপোর্ট
                    </li>
                </ul>
            </div>
            <div class="bg-gradient-to-br from-purple-50 to-blue-50 rounded-2xl p-8">
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                            <svg class="w-6 h-6 text-green-600" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.832-1.438A9.955 9.955 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">কাস্টমার</p>
                            <p class="text-xs text-gray-500">WhatsApp</p>
                        </div>
                    </div>
                    <div class="bg-gray-100 rounded-lg p-3 mb-4">
                        <p class="text-sm text-gray-700">"আমাদের পণ্যের দাম কত?"</p>
                    </div>
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-purple-600">AI রিপ্লাই</p>
                            <p class="text-xs text-gray-500">অটোমেটিক</p>
                        </div>
                    </div>
                    <div class="bg-purple-50 rounded-lg p-3">
                        <p class="text-sm text-gray-700">"আমাদের পণ্য ৫০০ টাকা থেকে শুরু হয়। আপনি কি অর্ডার করতে চান?"</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Feature 2 --}}
        <div class="grid lg:grid-cols-2 gap-12 items-center mb-20">
            <div class="order-2 lg:order-1 bg-gradient-to-br from-blue-50 to-cyan-50 rounded-2xl p-8">
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="font-bold text-gray-900 mb-4">লিড ড্যাশবোর্ড</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center text-purple-600 font-bold text-sm">র</div>
                                <span class="ml-3 text-sm font-medium text-gray-900">রাকিব হাসান</span>
                            </div>
                            <span class="text-xs text-green-600 bg-green-100 px-2 py-1 rounded-full">নতুন</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 font-bold text-sm">স</div>
                                <span class="ml-3 text-sm font-medium text-gray-900">সাবরিনা আক্তার</span>
                            </div>
                            <span class="text-xs text-blue-600 bg-blue-100 px-2 py-1 rounded-full">চলমান</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center text-green-600 font-bold text-sm">ক</div>
                                <span class="ml-3 text-sm font-medium text-gray-900">কামরুজ্জামান</span>
                            </div>
                            <span class="text-xs text-green-600 bg-green-100 px-2 py-1 rounded-full">সম্পন্ন</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="order-1 lg:order-2">
                <div class="inline-flex items-center bg-blue-100 text-blue-700 px-4 py-2 rounded-full text-sm font-medium mb-4">
                    লিড ম্যানেজমেন্ট
                </div>
                <h2 class="text-3xl font-bold text-gray-900 mb-4">লিড সংগ্রহ ও স্টোরেজ</h2>
                <p class="text-gray-600 mb-6">
                    কাস্টমার অর্ডার এবং মেসেজ অটোমেটিক্যালি লিড হিসেবে সংগ্রহ ও স্টোর করুন।
                    একটি জায়গায় সব লিড দেখুন এবং তাদের সহজে ম্যানেজ করুন।
                </p>
                <ul class="space-y-3">
                    <li class="flex items-center text-gray-600">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        অটোমেটিক লিড সংগ্রহ
                    </li>
                    <li class="flex items-center text-gray-600">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        একটি জায়গায় সব লিড
                    </li>
                    <li class="flex items-center text-gray-600">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        সহজ ম্যানেজমেন্ট
                    </li>
                </ul>
            </div>
        </div>

        {{-- Feature 3 --}}
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <div class="inline-flex items-center bg-green-100 text-green-700 px-4 py-2 rounded-full text-sm font-medium mb-4">
                    ইনভেন্টরি ম্যানেজমেন্ট
                </div>
                <h2 class="text-3xl font-bold text-gray-900 mb-4">পণ্য ও স্টক ম্যানেজমেন্ট</h2>
                <p class="text-gray-600 mb-6">
                    পণ্যের স্টক ট্র্যাক করুন, অটোমেটিক রিস্টক অ্যালার্ট পান এবং সেলস রিপোর্ট দেখুন।
                    আপনার ইনভেন্টরি সহজে পরিচালনা করুন।
                </p>
                <ul class="space-y-3">
                    <li class="flex items-center text-gray-600">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        স্টক ট্র্যাকিং
                    </li>
                    <li class="flex items-center text-gray-600">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        অটোমেটিক অ্যালার্ট
                    </li>
                    <li class="flex items-center text-gray-600">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        সেলস রিপোর্ট
                    </li>
                </ul>
            </div>
            <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl p-8">
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="font-bold text-gray-900 mb-4">ইনভেন্টরি স্ট্যাটাস</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-900">প্রোডাক্ট A</span>
                            <span class="text-xs text-green-600 bg-green-100 px-2 py-1 rounded-full">৪৫ পিস</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-900">প্রোডাক্ট B</span>
                            <span class="text-xs text-orange-600 bg-orange-100 px-2 py-1 rounded-full">৮ পিস</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-900">প্রোডাক্ট C</span>
                            <span class="text-xs text-red-600 bg-red-100 px-2 py-1 rounded-full">২ পিস</span>
                        </div>
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
