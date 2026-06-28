@extends('layouts.app')

@section('title', 'মূল্য - SocialBoost AI')

@section('content')
{{-- Hero Section --}}
<section class="py-20 bg-gradient-to-br from-purple-50 via-white to-blue-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-6">মূল্য পরিকল্পনা</h1>
        <p class="text-xl text-gray-600 max-w-2xl mx-auto">আপনার ব্যবসার জন্য সঠিক প্ল্যান বেছে নিন। সব প্ল্যানে ১৪ দিনের বিনামূল্যে ট্রায়াল অন্তর্ভুক্ত।</p>
    </div>
</section>

{{-- Pricing Cards --}}
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-3 gap-8">
            {{-- Starter Plan --}}
            <div class="bg-white border-2 border-gray-200 rounded-2xl p-8 card-hover">
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">স্টার্টার</h3>
                    <p class="text-gray-500">ছোট ব্যবসার জন্য আদর্শ</p>
                </div>
                
                <div class="text-center mb-8">
                    <div class="text-5xl font-bold text-gray-900 mb-2">১,৪৯৯</div>
                    <div class="text-gray-500">$/মাস</div>
                </div>

                <ul class="space-y-4 mb-8">
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-600">১টি WhatsApp নম্বর</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-600">১টি Facebook পেজ</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-600">৫০০টি AI রিপ্লাই/মাস</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-600">১০০টি লিড স্টোরেজ</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-600">বেসিক ইনভেন্টরি</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-600">ইমেইল সাপোর্ট</span>
                    </li>
                </ul>

                <a href="{{ url('/register') }}" class="block w-full text-center border-2 border-purple-600 text-purple-600 px-6 py-3 rounded-full font-semibold hover:bg-purple-50 transition">
                    বিনামূল্যে শুরু করুন
                </a>
            </div>

            {{-- Professional Plan --}}
            <div class="bg-gradient-to-br from-purple-600 to-blue-600 rounded-2xl p-8 card-hover text-white relative transform scale-105">
                <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                    <span class="bg-yellow-400 text-yellow-900 px-4 py-1 rounded-full text-sm font-bold">সবচেয়ে জনপ্রিয়</span>
                </div>
                
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2">প্রফেশনাল</h3>
                    <p class="text-white/70">মাঝারি ব্যবসার জন্য আদর্শ</p>
                </div>
                
                <div class="text-center mb-8">
                    <div class="text-5xl font-bold mb-2">৩,৪৯৯</div>
                    <div class="text-white/70">$/মাস</div>
                </div>

                <ul class="space-y-4 mb-8">
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-300 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>৩টি WhatsApp নম্বর</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-300 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>৫টি Facebook পেজ</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-300 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>২,০০০টি AI রিপ্লাই/মাস</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-300 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>১,০০০টি লিড স্টোরেজ</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-300 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>অ্যাডভান্সড ইনভেন্টরি</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-300 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>অ্যানালিটিক্স ড্যাশবোর্ড</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-300 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>প্রায়োরিটি সাপোর্ট</span>
                    </li>
                </ul>

                <a href="{{ url('/register') }}" class="block w-full text-center bg-white text-purple-600 px-6 py-3 rounded-full font-semibold hover:bg-gray-100 transition">
                    বিনামূল্যে শুরু করুন
                </a>
            </div>

            {{-- Enterprise Plan --}}
            <div class="bg-white border-2 border-gray-200 rounded-2xl p-8 card-hover">
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-purple-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">এন্টারপ্রাইজ</h3>
                    <p class="text-gray-500">বড় ব্যবসার জন্য আদর্শ</p>
                </div>
                
                <div class="text-center mb-8">
                    <div class="text-5xl font-bold text-gray-900 mb-2">৭,৯৯৯</div>
                    <div class="text-gray-500">$/মাস</div>
                </div>

                <ul class="space-y-4 mb-8">
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-600">অসীমিত WhatsApp নম্বর</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-600">অসীমিত Facebook পেজ</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-600">অসীমিত AI রিপ্লাই</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-600">অসীমিত লিড স্টোরেজ</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-600">কাস্টম AI ট্রেনিং</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-600">ডেডিকেটেড অ্যাকাউন্ট ম্যানেজার</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-600">API অ্যাক্সেস</span>
                    </li>
                </ul>

                <a href="{{ url('/contact') }}" class="block w-full text-center border-2 border-purple-600 text-purple-600 px-6 py-3 rounded-full font-semibold hover:bg-purple-50 transition">
                    যোগাযোগ করুন
                </a>
            </div>
        </div>
    </div>
</section>

{{-- FAQ Section --}}
<section class="py-20 bg-gray-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-gray-900 text-center mb-12">সচরাচর জিজ্ঞাসা</h2>
        
        <div class="space-y-4">
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <button onclick="this.nextElementSibling.classList.toggle('hidden'); this.querySelector('svg').classList.toggle('rotate-180')" class="w-full px-6 py-4 text-left flex items-center justify-between">
                    <span class="font-semibold text-gray-900">বিনামূল্যে ট্রায়াল কি আছে?</span>
                    <svg class="w-5 h-5 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div class="hidden px-6 pb-4 text-gray-600">
                    হ্যাঁ, আমরা ১৪ দিনের বিনামূল্যে ট্রায়াল প্রদান করি। কোনো ক্রেডিট কার্ড প্রয়োজন নেই।
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <button onclick="this.nextElementSibling.classList.toggle('hidden'); this.querySelector('svg').classList.toggle('rotate-180')" class="w-full px-6 py-4 text-left flex items-center justify-between">
                    <span class="font-semibold text-gray-900">প্ল্যান আপগ্রেড বা ডাউনগ্রেড করা যাক?</span>
                    <svg class="w-5 h-5 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div class="hidden px-6 pb-4 text-gray-600">
                    হ্যাঁ, আপনি যেকোনো সময় আপনার প্ল্যান আপগ্রেড বা ডাউনগ্রেড করতে পারবেন। পরিবর্তন পরবর্তী বিলিং সাইকেল থেকে কার্যকর হবে।
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <button onclick="this.nextElementSibling.classList.toggle('hidden'); this.querySelector('svg').classList.toggle('rotate-180')" class="w-full px-6 py-4 text-left flex items-center justify-between">
                    <span class="font-semibold text-gray-900">কোন পেমেন্ট মেথড সাপোর্টেড?</span>
                    <svg class="w-5 h-5 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div class="hidden px-6 pb-4 text-gray-600">
                    আমরা bKash, Nagad, Rocket, ক্রেডিট কার্ড, ডেবিট কার্ড এবং ব্যাংক ট্রান্সফার সাপোর্ট করি।
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <button onclick="this.nextElementSibling.classList.toggle('hidden'); this.querySelector('svg').classList.toggle('rotate-180')" class="w-full px-6 py-4 text-left flex items-center justify-between">
                    <span class="font-semibold text-gray-900">রিফান্ড পলিসি কি?</span>
                    <svg class="w-5 h-5 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div class="hidden px-6 pb-4 text-gray-600">
                    প্রথম ৭ দিনের মধ্যে ক্যানসেল করলে সম্পূর্ণ রিফান্ড পাবেন। তারপর বিলিং সাইকেলের শেষে রিফান্ড দেওয়া হবে।
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
