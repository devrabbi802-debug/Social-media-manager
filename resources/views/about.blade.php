@extends('layouts.app')

@section('title', 'আমাদের সম্পর্কে - SocialBoost AI')

@section('content')
{{-- Hero Section --}}
<section class="py-20 bg-gradient-to-br from-purple-50 via-white to-blue-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-6">আমাদের সম্পর্কে</h1>
        <p class="text-xl text-gray-600 max-w-2xl mx-auto">আমরা AI-চালিত সোশ্যাল মিডিয়া ম্যানেজমেন্ট প্ল্যাটফর্ম তৈরি করি</p>
    </div>
</section>

{{-- Mission Section --}}
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <h2 class="text-3xl font-bold text-gray-900 mb-6">আমাদের মিশন</h2>
                <p class="text-gray-600 mb-6">
                    আমাদের মিশন হলো ছোট এবং মাঝারি ব্যবসাগুলোকে AI-চালিত প্রযুক্তি দিয়ে ক্ষমতায়ন করা।
                    আমরা চাই প্রতিটি ব্যবসা যেন সোশ্যাল মিডিয়া এবং কাস্টমার সেবা সহজে পরিচালনা করতে পারে।
                </p>
                <p class="text-gray-600 mb-6">
                    আমাদের প্ল্যাটফর্ম WhatsApp এবং Facebook-এ AI-চালিত অটো-রিপ্লাই, লিড ম্যানেজমেন্ট,
                    ইনভেন্টরি ম্যানেজমেন্ট এবং অ্যানালিটিক্স সরবরাহ করে।
                </p>
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <div class="text-3xl font-bold gradient-text mb-2">৫০০+</div>
                        <p class="text-gray-600">সক্রিয় ব্যবহারকারী</p>
                    </div>
                    <div>
                        <div class="text-3xl font-bold gradient-text mb-2">১০,০০০+</div>
                        <p class="text-gray-600">AI রিপ্লাই প্রতিদিন</p>
                    </div>
                    <div>
                        <div class="text-3xl font-bold gradient-text mb-2">৯৮%</div>
                        <p class="text-gray-600">কাস্টমার সন্তুষ্টি</p>
                    </div>
                    <div>
                        <div class="text-3xl font-bold gradient-text mb-2">২৪/৭</div>
                        <p class="text-gray-600">সাপোর্ট</p>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-br from-purple-50 to-blue-50 rounded-2xl p-8">
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="font-bold text-gray-900 mb-4">আমাদের মূল্যবোধ</h3>
                    <ul class="space-y-4">
                        <li class="flex items-start">
                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">উদ্ভাবন</h4>
                                <p class="text-sm text-gray-600">সবসময় নতুন প্রযুক্তি ব্যবহার করি</p>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">কাস্টমার ফোকাস</h4>
                                <p class="text-sm text-gray-600">কাস্টমারদের সফলতা আমাদের সফলতা</p>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">গুণমান</h4>
                                <p class="text-sm text-gray-600">সর্বোচ্চ মানদণ্ড বজায় রাখি</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Team Section --}}
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">আমাদের টিম</h2>
            <p class="text-xl text-gray-600">অভিজ্ঞ পেশাদারদের দল</p>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
            <div class="bg-white rounded-2xl p-6 text-center shadow-sm card-hover">
                <div class="w-20 h-20 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="text-2xl font-bold text-purple-600">ক</span>
                </div>
                <h3 class="text-lg font-bold text-gray-900">কামরুজ্জামান</h3>
                <p class="text-purple-600 mb-3">সিইও ও প্রতিষ্ঠাতা</p>
                <p class="text-sm text-gray-600">১০+ বছরের অভিজ্ঞতা</p>
            </div>
            <div class="bg-white rounded-2xl p-6 text-center shadow-sm card-hover">
                <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="text-2xl font-bold text-blue-600">র</span>
                </div>
                <h3 class="text-lg font-bold text-gray-900">রাকিব হাসান</h3>
                <p class="text-blue-600 mb-3">সিটিও</p>
                <p class="text-sm text-gray-600">AI ও মেশিন লার্নিং বিশেষজ্ঞ</p>
            </div>
            <div class="bg-white rounded-2xl p-6 text-center shadow-sm card-hover">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="text-2xl font-bold text-green-600">স</span>
                </div>
                <h3 class="text-lg font-bold text-gray-900">সাবরিনা আক্তার</h3>
                <p class="text-green-600 mb-3">সিএমও</p>
                <p class="text-sm text-gray-600">মার্কেটিং ও গ্রোথ বিশেষজ্ঞ</p>
            </div>
        </div>
    </div>
</section>

{{-- CTA Section --}}
<section class="py-20 gradient-bg">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl lg:text-4xl font-bold text-white mb-6">আমাদের সাথে যুক্ত হোন</h2>
        <p class="text-xl text-white/80 mb-8">আজই শুরু করুন এবং আপনার ব্যবসা রূপান্তরিত করুন</p>
        <a href="{{ url('/register') }}" class="inline-block bg-white text-purple-600 px-8 py-4 rounded-full font-semibold text-lg hover:bg-gray-100 transition">
            বিনামূল্যে শুরু করুন
        </a>
    </div>
</section>
@endsection
