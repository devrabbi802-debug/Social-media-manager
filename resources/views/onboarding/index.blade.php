<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>বিজনেস সেটআপ — SocialBoost AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Hind Siliguri', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .gradient-text { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .step-enter { animation: stepEnter 0.3s ease-out; }
        @keyframes stepEnter { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: translateX(0); } }
    </style>
</head>
<body class="bg-gray-50 min-h-screen" x-data="onboarding()" x-init="init()">

    {{-- Top Progress Bar --}}
    <div class="fixed top-0 left-0 right-0 z-50 bg-white shadow-sm">
        <div class="h-1.5 bg-gray-200">
            <div class="h-full gradient-bg transition-all duration-500" :style="`width: ${(currentStep / totalSteps) * 100}%`"></div>
        </div>
        <div class="max-w-4xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="/" class="flex items-center space-x-2">
                <div class="w-8 h-8 gradient-bg rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <span class="text-lg font-bold gradient-text">SocialBoost AI</span>
            </a>
            <div class="text-sm text-gray-500">
                ধাপ <span x-text="currentStep" class="font-bold text-purple-600"></span> / <span x-text="totalSteps"></span>
            </div>
        </div>
    </div>

    {{-- Main Form --}}
    <form method="POST" action="{{ url('/onboarding') }}" enctype="multipart/form-data" id="onboardingForm">
        @csrf

        <div class="max-w-3xl mx-auto px-4 pt-24 pb-32">

            {{-- Error Display --}}
            @if ($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl text-sm">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            {{-- Step 1: Account --}}
            <div x-show="currentStep === 1" x-transition class="step-enter">
                <div class="text-center mb-8">
                    <div class="w-16 h-16 gradient-bg rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">অ্যাকাউন্ট তৈরি করুন</h2>
                    <p class="text-gray-500 mt-2">আপনার ব্যক্তিগত তথ্য দিন</p>
                </div>

                <div class="bg-white rounded-2xl shadow-xl p-8 space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">পুরো নাম *</label>
                        <input type="text" name="name" x-model="form.name" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                               placeholder="আপনার পুরো নাম">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ইমেইল *</label>
                        <input type="email" name="email" x-model="form.email" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                               placeholder="example@email.com">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">মোবাইল নম্বর *</label>
                        <input type="tel" name="phone" x-model="form.phone" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                               placeholder="01XXXXXXXXX">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">সাবডোমেইন *</label>
                        <div class="flex">
                            <div class="relative flex-1">
                                <input type="text" name="subdomain" x-model="form.subdomain" required
                                       @input="form.subdomain = form.subdomain.toLowerCase().replace(/[^a-z0-9-]/g, '').replace(/\s+/g, ''); checkSubdomain()"
                                       class="w-full px-4 py-3 border rounded-l-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent pr-10"
                                       :class="{
                                           'border-gray-300': !form.subdomain || subdomainChecking,
                                           'border-green-500': form.subdomain && !subdomainChecking && subdomainAvailable === true,
                                           'border-red-500': form.subdomain && !subdomainChecking && subdomainAvailable === false
                                       }"
                                       placeholder="yourshopname">
                                <div class="absolute right-3 top-1/2 -translate-y-1/2">
                                    <template x-if="subdomainChecking">
                                        <svg class="w-5 h-5 text-gray-400 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    </template>
                                    <template x-if="!subdomainChecking && subdomainAvailable === true">
                                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    </template>
                                    <template x-if="!subdomainChecking && subdomainAvailable === false">
                                        <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                                    </template>
                                </div>
                            </div>
                            <span class="px-4 py-3 bg-gray-100 border border-l-0 border-gray-300 rounded-r-xl text-gray-600 text-sm">.{{ config('app.domain') }}</span>
                        </div>
                        <div class="mt-1 flex items-center space-x-2">
                            <template x-if="form.subdomain && !subdomainChecking && subdomainAvailable === true">
                                <p class="text-xs text-green-600 flex items-center">
                                    <span x-text="form.subdomain + '.{{ config('app.domain') }}'"></span> উপলব্ধ!
                                </p>
                            </template>
                            <template x-if="form.subdomain && !subdomainChecking && subdomainAvailable === false">
                                <p class="text-xs text-red-600">এই সাবডোমেইন ইতিমধ্যে নেওয়া হয়েছে। অন্য নাম বাছুন।</p>
                            </template>
                            <template x-if="!form.subdomain">
                                <p class="text-xs text-gray-400">শুধু ছোট অক্ষর, সংখ্যা ও হাইফেন — স্পেস বা স্পেশাল ক্যারেক্টার দেওয়া যাবে না</p>
                            </template>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">পাসওয়ার্ড *</label>
                            <input type="password" name="password" x-model="form.password" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                   placeholder="কমপক্ষে ৮ অক্ষর">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">পাসওয়ার্ড নিশ্চিত *</label>
                            <input type="password" name="password_confirmation" x-model="form.password_confirmation" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                   placeholder="আবার লিখুন">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Step 2: Business Info --}}
            <div x-show="currentStep === 2" x-transition class="step-enter">
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">বিজনেস তথ্য</h2>
                    <p class="text-gray-500 mt-2">আপনার ব্যবসার বিস্তারিত দিন</p>
                </div>

                <div class="bg-white rounded-2xl shadow-xl p-8 space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">বিজনেসের নাম *</label>
                        <input type="text" name="business_name" x-model="form.business_name" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                               placeholder="যেমন: রিয়া'স ফ্যাশন হাউজ">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">বিজনেস ক্যাটাগরি *</label>
                        <div class="relative" x-data="{ open: false, search: '' }" @click.outside="open = false">
                            <input type="hidden" name="category_id" :value="form.category_id">
                            <input type="hidden" name="custom_category_name" :value="form.custom_category_name">

                            {{-- Selected display / trigger --}}
                            <div @click="open = !open"
                                 class="w-full px-4 py-3 border border-gray-300 rounded-xl cursor-pointer flex items-center justify-between bg-white"
                                 :class="{ 'ring-2 ring-purple-500 border-transparent': open }">
                                <span x-text="selectedCategoryName || 'ক্যাটাগরি খুঁজুন বা লিখুন...'" :class="{ 'text-gray-400': !selectedCategoryName }"></span>
                                <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>

                            {{-- Dropdown --}}
                            <div x-show="open" x-transition
                                 class="absolute z-50 mt-2 w-full bg-white border border-gray-200 rounded-xl shadow-xl max-h-72 overflow-y-auto">

                                {{-- Search input --}}
                                <div class="sticky top-0 bg-white p-3 border-b">
                                    <input type="text" x-model="search" placeholder="ক্যাটাগরি খুঁজুন..."
                                           @click.stop
                                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                </div>

                                {{-- Default categories list --}}
                                <template x-for="cat in filteredCategories" :key="cat.id">
                                    <div @click="form.category_id = cat.id; form.custom_category_name = ''; search = ''; open = false"
                                         class="px-4 py-3 cursor-pointer hover:bg-purple-50 flex items-center space-x-3 transition"
                                         :class="{ 'bg-purple-50 border-l-4 border-purple-500': form.category_id == cat.id }">
                                        <span class="text-xl" x-text="cat.icon"></span>
                                        <span class="text-sm font-medium text-gray-700" x-text="cat.name"></span>
                                        <span class="text-xs text-gray-400" x-text="'(' + (cat.extra_fields || []).length + ' fields)'"></span>
                                    </div>
                                </template>

                                {{-- No results found --}}
                                <template x-if="search && filteredCategories.length === 0">
                                    <div @click="form.category_id = ''; form.custom_category_name = search; open = false"
                                         class="px-4 py-3 cursor-pointer hover:bg-green-50 flex items-center space-x-3 border-t">
                                        <span class="text-xl">➕</span>
                                        <div>
                                            <span class="text-sm font-medium text-green-700">"<span x-text="search"></span>" যোগ করুন</span>
                                            <p class="text-xs text-gray-400">নতুন ক্যাটাগরি হিসেবে তৈরি হবে</p>
                                        </div>
                                    </div>
                                </template>

                                {{-- Default message --}}
                                <template x-if="!search && filteredCategories.length > 0">
                                    <div class="px-4 py-2 text-xs text-gray-400 border-t">
                                        ডিফল্ট ক্যাটাগরি বাছুন অথবা নাম লিখে নতুন তৈরি করুন
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Custom category name display --}}
                        <template x-if="form.custom_category_name">
                            <div class="mt-2 flex items-center space-x-2 bg-green-50 border border-green-200 rounded-lg px-4 py-2">
                                <span class="text-green-600">✓</span>
                                <span class="text-sm text-green-700">নতুন ক্যাটাগরি: <strong x-text="form.custom_category_name"></strong></span>
                                <button type="button" @click="form.custom_category_name = ''; form.category_id = ''" class="text-red-400 hover:text-red-600 ml-auto">×</button>
                            </div>
                        </template>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">উপ-ক্যাটাগরি (ঐচ্ছিক)</label>
                        <input type="text" name="sub_category" x-model="form.sub_category"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                               placeholder="যেমন: মহিলাদের শাড়ি ও থ্রি-পিস">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">AI Persona নাম *</label>
                        <input type="text" name="persona_name" x-model="form.persona_name" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                               placeholder="AI এর নাম, যেমন: রিয়া">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">বিজনেস সময় *</label>
                        <input type="text" name="business_hours" x-model="form.business_hours" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                               placeholder="যেমন: সকাল ১০টা – রাত ৯টা">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">অফ-আয়ার মেসেজ</label>
                        <input type="text" name="off_hours_message" x-model="form.off_hours_message"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                               placeholder="যেমন: আমরা এখন অফলাইনে, সকাল ১০টায় রিপ্লাই দিব">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">বিজনেস বিবরণ *</label>
                        <textarea name="business_description" x-model="form.business_description" required rows="3"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                  placeholder="AI যেন নিজের বিজনেস সম্পর্কে বলতে পারে (১-২ লাইন)"></textarea>
                    </div>
                </div>
            </div>

            {{-- Step 3: Tone & Communication --}}
            <div x-show="currentStep === 3" x-transition class="step-enter">
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-gradient-to-r from-orange-500 to-red-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 4.418 9 8z"/></svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">টোন ও কমিউনিকেশন</h2>
                    <p class="text-gray-500 mt-2">AI কিভাবে কথা বলবে সেটা সেট করুন</p>
                </div>

                <div class="bg-white rounded-2xl shadow-xl p-8 space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">ফর্মালিটি লেভেল *</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="relative cursor-pointer">
                                <input type="radio" name="formality_level" value="formal" x-model="form.formality_level" class="peer sr-only">
                                <div class="border-2 border-gray-200 peer-checked:border-purple-500 peer-checked:bg-purple-50 rounded-xl p-4 text-center transition">
                                    <div class="text-2xl mb-1"> Formal</div>
                                    <div class="text-sm text-gray-500">পেশাদার ভাষা</div>
                                </div>
                            </label>
                            <label class="relative cursor-pointer">
                                <input type="radio" name="formality_level" value="casual" x-model="form.formality_level" class="peer sr-only" checked>
                                <div class="border-2 border-gray-200 peer-checked:border-purple-500 peer-checked:bg-purple-50 rounded-xl p-4 text-center transition">
                                    <div class="text-2xl mb-1"> Casual</div>
                                    <div class="text-sm text-gray-500">বন্ধুসুলভ ভাষা</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">ইমোজি ব্যবহার *</label>
                        <div class="grid grid-cols-3 gap-3">
                            <label class="relative cursor-pointer">
                                <input type="radio" name="emoji_usage" value="never" x-model="form.emoji_usage" class="peer sr-only">
                                <div class="border-2 border-gray-200 peer-checked:border-purple-500 peer-checked:bg-purple-50 rounded-xl p-3 text-center transition">
                                    <div class="text-lg font-medium"> না</div>
                                    <div class="text-xs text-gray-500">কখনো না</div>
                                </div>
                            </label>
                            <label class="relative cursor-pointer">
                                <input type="radio" name="emoji_usage" value="sometimes" x-model="form.emoji_usage" class="peer sr-only" checked>
                                <div class="border-2 border-gray-200 peer-checked:border-purple-500 peer-checked:bg-purple-50 rounded-xl p-3 text-center transition">
                                    <div class="text-lg font-medium"> মাঝে মাঝে</div>
                                    <div class="text-xs text-gray-500">কখনো কখনো</div>
                                </div>
                            </label>
                            <label class="relative cursor-pointer">
                                <input type="radio" name="emoji_usage" value="often" x-model="form.emoji_usage" class="peer sr-only">
                                <div class="border-2 border-gray-200 peer-checked:border-purple-500 peer-checked:bg-purple-50 rounded-xl p-3 text-center transition">
                                    <div class="text-lg font-medium"> বেশি</div>
                                    <div class="text-xs text-gray-500">প্রায় সবসময়</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">ভাষা স্টাইল *</label>
                        <div class="grid grid-cols-3 gap-3">
                            <label class="relative cursor-pointer">
                                <input type="radio" name="language_style" value="shuddho_bangla" x-model="form.language_style" class="peer sr-only">
                                <div class="border-2 border-gray-200 peer-checked:border-purple-500 peer-checked:bg-purple-50 rounded-xl p-3 text-center transition">
                                    <div class="text-sm font-medium">শুদ্ধ বাংলা</div>
                                </div>
                            </label>
                            <label class="relative cursor-pointer">
                                <input type="radio" name="language_style" value="anjonio" x-model="form.language_style" class="peer sr-only">
                                <div class="border-2 border-gray-200 peer-checked:border-purple-500 peer-checked:bg-purple-50 rounded-xl p-3 text-center transition">
                                    <div class="text-sm font-medium">আঞ্চলিক</div>
                                </div>
                            </label>
                            <label class="relative cursor-pointer">
                                <input type="radio" name="language_style" value="banglish" x-model="form.language_style" class="peer sr-only" checked>
                                <div class="border-2 border-gray-200 peer-checked:border-purple-500 peer-checked:bg-purple-50 rounded-xl p-3 text-center transition">
                                    <div class="text-sm font-medium">Banglish</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">গ্রিটিং স্টাইল *</label>
                        <input type="text" name="greeting_style" x-model="form.greeting_style" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                               placeholder="যেমন: আসসালামু আলাইকুম / হ্যালো / নমস্কার">
                    </div>
                </div>
            </div>

            {{-- Step 4: Pricing Policy --}}
            <div x-show="currentStep === 4" x-transition class="step-enter">
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">প্রাইসিং নীতি</h2>
                    <p class="text-gray-500 mt-2">দাম ও ছাড় সম্পর্কে নীতি সেট করুন</p>
                </div>

                <div class="bg-white rounded-2xl shadow-xl p-8 space-y-6">
                    <div class="flex items-center justify-between py-3">
                        <div>
                            <p class="font-medium text-gray-900">দরদাম করা যাবে?</p>
                            <p class="text-sm text-gray-500">AI কি কাস্টমারের সাথে দরদাম করতে পারবে</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="price_negotiation" value="1" x-model="form.price_negotiation" class="sr-only peer">
                            <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-purple-600"></div>
                        </label>
                    </div>

                    <div x-show="form.price_negotiation" x-transition>
                        <label class="block text-sm font-medium text-gray-700 mb-1">সর্বোচ্চ ছাড় (%)</label>
                        <input type="number" name="negotiation_limit" x-model="form.negotiation_limit" min="0" max="100"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                               placeholder="যেমন: 10">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">বাল্ক ডিসকাউন্ট রুল</label>
                        <input type="text" name="bulk_discount_rule" x-model="form.bulk_discount_rule"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                               placeholder="যেমন: ৫+ পিস কিনলে ১০% ছাড়">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">বর্তমান প্রোমো টেক্সট (ঐচ্ছিক)</label>
                        <input type="text" name="current_promo" x-model="form.current_promo"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                               placeholder="যেমন: ঈদ অফার: ১০% ছাড়">
                    </div>
                </div>
            </div>

            {{-- Step 5: Delivery & Payment --}}
            <div x-show="currentStep === 5" x-transition class="step-enter">
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-gradient-to-r from-indigo-500 to-violet-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">ডেলিভারি ও পেমেন্ট</h2>
                    <p class="text-gray-500 mt-2">ডেলিভারি ও পেমেন্ট তথ্য দিন</p>
                </div>

                <div class="bg-white rounded-2xl shadow-xl p-8 space-y-6">
                    <h3 class="font-semibold text-gray-900 border-b pb-2">ডেলিভারি</h3>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ডেলিভারি এরিয়া ও চার্জ</label>
                        <p class="text-xs text-gray-400 mb-3">প্রতিটি এরিয়ার নাম ও চার্জ দিন (যেমন: Inside Dhaka — 60৳)</p>

                        <div class="space-y-3">
                            <template x-for="(area, index) in deliveryAreas" :key="index">
                                <div class="flex items-start gap-3 bg-gray-50 rounded-xl p-3">
                                    <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <input type="text" x-model="area.name"
                                                   class="w-full px-3 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm"
                                                   placeholder="এরিয়ার নাম (যেমন: Inside Dhaka)">
                                        </div>
                                        <div>
                                            <input type="text" x-model="area.price"
                                                   class="w-full px-3 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm"
                                                   placeholder="চার্জ (যেমন: 60৳)">
                                        </div>
                                    </div>
                                    <button type="button" @click="deliveryAreas.splice(index, 1)"
                                            x-show="deliveryAreas.length > 1"
                                            class="mt-1 text-red-400 hover:text-red-600 transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </template>
                        </div>

                        <button type="button" @click="deliveryAreas.push({name: '', price: ''})"
                                class="mt-3 flex items-center space-x-2 text-purple-600 hover:text-purple-700 text-sm font-medium transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            <span>এরিয়া যোগ করুন</span>
                        </button>

                        <input type="hidden" name="delivery_areas" :value="JSON.stringify(deliveryAreas.filter(a => a.name.trim()))">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ডেলিভারি সময়</label>
                            <input type="text" name="delivery_time" x-model="form.delivery_time"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                   placeholder="যেমন: ৩-৫ দিন">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ডেলিভারি পার্টনার</label>
                            <input type="text" name="delivery_partner" x-model="form.delivery_partner"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                   placeholder="যেমন: পাঠাও/স্টেডফাস্ট">
                        </div>
                    </div>
                    <div class="flex items-center justify-between py-3">
                        <div>
                            <p class="font-medium text-gray-900">Cash on Delivery আছে?</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="cod_available" value="1" x-model="form.cod_available" class="sr-only peer" checked>
                            <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-purple-600"></div>
                        </label>
                    </div>

                    <h3 class="font-semibold text-gray-900 border-b pb-2 pt-4">পেমেন্ট</h3>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">গ্রহণযোগ্য পেমেন্ট মেথড</label>
                        <p class="text-xs text-gray-400 mb-3">প্রতিটি পেমেন্ট মেথডের নাম ও বিস্তারিত তথ্য দিন (যেমন: bKash — 01712345678)</p>

                        <div class="space-y-3">
                            <template x-for="(method, index) in paymentMethods" :key="index">
                                <div class="flex items-start gap-3 bg-gray-50 rounded-xl p-3">
                                    <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <input type="text" x-model="method.name"
                                                   class="w-full px-3 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm"
                                                   placeholder="মেথডের নাম (যেমন: bKash)">
                                        </div>
                                        <div>
                                            <input type="text" x-model="method.details"
                                                   class="w-full px-3 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm"
                                                   placeholder="বিস্তারিত (যেমন: 01712345678)">
                                        </div>
                                    </div>
                                    <button type="button" @click="paymentMethods.splice(index, 1)"
                                            x-show="paymentMethods.length > 1"
                                            class="mt-1 text-red-400 hover:text-red-600 transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </template>
                        </div>

                        <button type="button" @click="paymentMethods.push({name: '', details: ''})"
                                class="mt-3 flex items-center space-x-2 text-purple-600 hover:text-purple-700 text-sm font-medium transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            <span>পেমেন্ট মেথড যোগ করুন</span>
                        </button>

                        <input type="hidden" name="accepted_payment_methods" :value="JSON.stringify(paymentMethods.filter(m => m.name.trim()))">
                    </div>
                    <div class="flex items-center justify-between py-3">
                        <div>
                            <p class="font-medium text-gray-900">অ্যাডভান্স পেমেন্ট লাগবে?</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="advance_payment_required" value="1" x-model="form.advance_payment_required" class="sr-only peer">
                            <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-purple-600"></div>
                        </label>
                    </div>
                    <div x-show="form.advance_payment_required" x-transition>
                        <label class="block text-sm font-medium text-gray-700 mb-1">অ্যাডভান্স পারসেন্টেজ (%)</label>
                        <input type="number" name="advance_payment_percent" x-model="form.advance_payment_percent" min="0" max="100"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                               placeholder="যেমন: 50">
                    </div>

                    <div class="flex items-center justify-between py-3">
                        <div>
                            <p class="font-medium text-gray-900">ঢাকার বাইরে অ্যাডভান্স লাগবে?</p>
                            <p class="text-xs text-gray-400">বাইরের অর্ডারে অগ্রিম পেমেন্ট বাধ্যতামূলক</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="advance_for_outside_dhaka" value="1" x-model="form.advance_for_outside_dhaka" class="sr-only peer">
                            <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-purple-600"></div>
                        </label>
                    </div>

                    <h3 class="font-semibold text-gray-900 border-b pb-2 pt-4">নীতিমালা</h3>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">রিফান্ড নীতি</label>
                        <textarea name="refund_policy" x-model="form.refund_policy" rows="3"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                  placeholder="যেমন: পণ্য হাতে পেয়ে ৩ দিনের মধ্যে রিফান্ড সম্ভব..."></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">এক্সচেঞ্জ নীতি</label>
                        <textarea name="exchange_policy" x-model="form.exchange_policy" rows="3"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                  placeholder="যেমন: ৭ দিনের মধ্যে এক্সচেঞ্জ সম্ভব, পণ্য অব্যবহৃত হতে হবে..."></textarea>
                    </div>

                    <h3 class="font-semibold text-gray-900 border-b pb-2 pt-4">অর্ডার প্রসেস</h3>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">অর্ডার মেসেজ</label>
                        <p class="text-xs text-gray-400 mb-2">কাস্টমার যখন অর্ডার দিতে চাইবে, AI এই মেসেজটি পাঠাবে।</p>
                        <textarea name="order_process_message" x-model="form.order_process_message" rows="8"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent font-mono text-sm"
                                  placeholder="অর্ডার প্রসেস&#10;নিম্নের তথ্যগুলো দিন👇।&#10;&#10;নাম:&#10;কন্টাক্ট নাম্বার:&#10;ঠিকানা:&#10;&#10;গুরুত্বপূর্ণ বিষয়: প্রতিটি তথ্য অবশ্যই ইংরেজিতে দিতে হবে।"></textarea>
                    </div>
                </div>
            </div>

            {{-- Step 6: Custom FAQ --}}
            <div x-show="currentStep === 6" x-transition class="step-enter">
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-gradient-to-r from-pink-500 to-rose-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">কাস্টম FAQ</h2>
                    <p class="text-gray-500 mt-2">আপনার বিজনেস-স্পেসিফিক প্রশ্ন-উত্তর যোগ করুন</p>
                </div>

                <div class="bg-white rounded-2xl shadow-xl p-8 space-y-4">
                    <template x-for="(item, index) in faq" :key="index">
                        <div class="border border-gray-200 rounded-xl p-4 space-y-3 relative">
                            <button type="button" @click="removeFaq(index)"
                                    class="absolute top-2 right-2 text-red-400 hover:text-red-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">প্রশ্ন</label>
                                <input :name="'faq[' + index + '][question]'" x-model="item.question"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm"
                                       placeholder="শোরুম কোথায়?">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">উত্তর</label>
                                <textarea :name="'faq[' + index + '][answer]'" x-model="item.answer" rows="2"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm"
                                          placeholder="আমাদের শোরুম ঢাকায় অবস্থিত..."></textarea>
                            </div>
                        </div>
                    </template>

                    <button type="button" @click="addFaq()"
                            class="w-full border-2 border-dashed border-purple-300 text-purple-600 rounded-xl py-3 hover:bg-purple-50 transition font-medium">
                        + প্রশ্ন-উত্তর যোগ করুন
                    </button>
                </div>
            </div>

            {{-- Step 7: Escalation Rules --}}
            <div x-show="currentStep === 7" x-transition class="step-enter">
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-gradient-to-r from-red-500 to-pink-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">এসক্যালেশন রুলস</h2>
                    <p class="text-gray-500 mt-2">কোন কীওয়ার্ডে মানুষকে হ্যান্ডওভার করবে</p>
                </div>

                <div class="bg-white rounded-2xl shadow-xl p-8 space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">এসক্যালেশন কীওয়ার্ড</label>
                        <input type="text" name="custom_escalation_keywords" x-model="form.custom_escalation_keywords"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                               placeholder="যেমন: ম্যানেজার, অভিযোগ, রিফান্ড, ক্যান্সেল">
                        <p class="text-xs text-gray-400 mt-1">কমা দিয়ে আলাদা করুন। এই কীওয়ার্ড এলে AI human agent-কে হ্যান্ডওভার করবে।</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">হিউমান যোগাযোগ তথ্য</label>
                        <input type="text" name="escalation_contact" x-model="form.escalation_contact"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                               placeholder="যেমন: 01XXXXXXXXX বা support@email.com">
                    </div>
                </div>
            </div>

            {{-- Step 8: Logo --}}
            <div x-show="currentStep === 8" x-transition class="step-enter">
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-gradient-to-r from-teal-500 to-cyan-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
             ক্যাটাগরি-স্পেসিফিক তথ্য
           <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">কোম্পানি লোগো</h2>
                    <p class="text-gray-500 mt-2">আপনার ব্যবসার লোগো আপলোড করুন (ঐচ্ছিক)</p>
                </div>

                <div class="bg-white rounded-2xl shadow-xl p-8">
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-purple-400 transition"
                         x-data="{ dragging: false }"
                         @dragover.prevent="dragging = true"
                         @dragleave.prevent="dragging = false"
                         @drop.prevent="dragging = false; $refs.logoInput.files = $event.dataTransfer.files; form.logo = $event.dataTransfer.files[0]"
                         :class="{ 'border-purple-500 bg-purple-50': dragging }">
                        <template x-if="!logoPreview">
                            <div>
                                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <p class="text-gray-500 mb-2">লোগো এখানে ড্র্যাগ করুন বা</p>
                                <label class="inline-block cursor-pointer gradient-bg text-white px-6 py-2 rounded-full font-medium hover:opacity-90 transition">
                                    ফাইল বাছুন
                                    <input type="file" name="logo" x-ref="logoInput" accept="image/*" class="hidden"
                                           @change="if($refs.logoInput.files[0]) { logoPreview = URL.createObjectURL($refs.logoInput.files[0]); form.logo = $refs.logoInput.files[0]; }">
                                </label>
                                <p class="text-xs text-gray-400 mt-2">PNG, JPG, SVG (সর্বোচ্চ ২MB)</p>
                            </div>
                        </template>
                        <template x-if="logoPreview">
                            <div>
                                <img :src="logoPreview" class="max-h-40 mx-auto mb-4 rounded-lg">
                                <button type="button" @click="logoPreview = null; form.logo = null; $refs.logoInput.value = ''"
                                        class="text-red-500 hover:text-red-700 text-sm font-medium">সরান</button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Step Navigation Buttons --}}
            <div class="fixed bottom-0 left-0 right-0 bg-white border-t shadow-lg z-40">
                <div class="max-w-3xl mx-auto px-4 py-4 flex items-center justify-between">
                    <button type="button" @click="prevStep()" x-show="currentStep > 1"
                            class="flex items-center space-x-2 text-gray-600 hover:text-purple-600 transition font-medium px-6 py-3 rounded-xl hover:bg-gray-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        <span>পূর্ববর্তী</span>
                    </button>
                    <div x-show="currentStep === 1"></div>

                    {{-- Error message --}}
                    <div x-show="stepError" x-transition class="text-red-500 text-sm font-medium" x-text="stepError"></div>

                    <button type="button" @click="nextStep()" x-show="currentStep < totalSteps"
                            class="flex items-center space-x-2 gradient-bg text-white px-8 py-3 rounded-xl font-semibold hover:opacity-90 transition ml-auto">
                        <span>পরবর্তী</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>

                    <button type="submit" x-show="currentStep === totalSteps"
                            class="flex items-center space-x-2 bg-green-600 text-white px-8 py-3 rounded-xl font-semibold hover:bg-green-700 transition ml-auto">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>সম্পন্ন করুন</span>
                    </button>
                </div>
            </div>
        </div>
    </form>

    <script>
        function onboarding() {
            return {
                currentStep: 1,
                totalSteps: 8,
                logoPreview: null,
                stepError: '',
                categories: @json($categories),
                subdomainAvailable: null,
                subdomainChecking: false,
                subdomainTimeout: null,

                form: {
                    name: '', email: '', phone: '', subdomain: '', password: '', password_confirmation: '',
                    business_name: '', category_id: '', custom_category_name: '', sub_category: '', persona_name: '',
                    business_hours: '', off_hours_message: '', business_description: '',
                    formality_level: 'casual', emoji_usage: 'sometimes', language_style: 'banglish', greeting_style: 'হ্যালো',
                    price_negotiation: false, negotiation_limit: 0, bulk_discount_rule: '', current_promo: '',
                    delivery_time: '', delivery_partner: '', cod_available: true,
                    advance_payment_required: false, advance_payment_percent: 0, advance_for_outside_dhaka: false,
                    refund_policy: '', exchange_policy: '', order_process_message: '',
                    custom_escalation_keywords: '', escalation_contact: '',
                },

                extraFields: {},
                faq: [{ question: '', answer: '' }],
                paymentMethods: [{ name: '', details: '' }],
                deliveryAreas: [{ name: 'Inside Dhaka', price: '' }, { name: 'Outside Dhaka', price: '' }],

                init() {
                    this.loadExistingData();
                },

                get selectedCategoryName() {
                    if (this.form.custom_category_name) return this.form.custom_category_name;
                    const cat = this.categories.find(c => c.id == this.form.category_id);
                    return cat ? cat.name : '';
                },

                get filteredCategories() {
                    if (!this.search) return this.categories;
                    const q = this.search.toLowerCase();
                    return this.categories.filter(c => c.name.toLowerCase().includes(q));
                },

                get selectedExtraFields() {
                    if (this.form.custom_category_name) return [];
                    const cat = this.categories.find(c => c.id == this.form.category_id);
                    return cat ? (cat.extra_fields || []) : [];
                },

                checkSubdomain() {
                    clearTimeout(this.subdomainTimeout);
                    this.subdomainAvailable = null;

                    if (!this.form.subdomain || this.form.subdomain.length < 3) {
                        this.subdomainChecking = false;
                        return;
                    }

                    this.subdomainChecking = true;

                    this.subdomainTimeout = setTimeout(() => {
                        fetch('{{ url("/check-subdomain") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ subdomain: this.form.subdomain })
                        })
                        .then(res => res.json())
                        .then(data => {
                            this.subdomainAvailable = data.available;
                            this.subdomainChecking = false;
                        })
                        .catch(() => {
                            this.subdomainChecking = false;
                        });
                    }, 500);
                },

                validateStep(step) {
                    this.stepError = '';
                    const errors = [];

                    if (step === 1) {
                        if (!this.form.name.trim()) errors.push('পুরো নাম দিন');
                        if (!this.form.email.trim()) errors.push('ইমেইল দিন');
                        else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.form.email)) errors.push('সঠিক ইমেইল দিন');
                        if (!this.form.phone.trim()) errors.push('মোবাইল নম্বর দিন');
                        if (!this.form.subdomain.trim()) errors.push('সাবডোমেইন দিন');
                        else if (!/^[a-z0-9-]+$/.test(this.form.subdomain)) errors.push('সাবডোমেইন শুধু ছোট অক্ষর, সংখ্যা ও হাইফেন হতে হবে');
                        else if (this.subdomainAvailable === false) errors.push('এই সাবডোমেইন ইতিমধ্যে নেওয়া হয়েছে');
                        else if (this.subdomainAvailable === null && this.form.subdomain.length >= 3) errors.push('সাবডোমেইন যাচাই করা হচ্ছে, অপেক্ষা করুন');
                        if (!this.form.password) errors.push('পাসওয়ার্ড দিন');
                        else if (this.form.password.length < 8) errors.push('পাসওয়ার্ড কমপক্ষে ৮ অক্ষর হতে হবে');
                        if (this.form.password !== this.form.password_confirmation) errors.push('পাসওয়ার্ড মিলছে না');
                    }

                    if (step === 2) {
                        if (!this.form.business_name.trim()) errors.push('বিজনেসের নাম দিন');
                        if (!this.form.category_id && !this.form.custom_category_name.trim()) errors.push('ক্যাটাগরি বাছুন বা নতুন লিখুন');
                        if (!this.form.persona_name.trim()) errors.push('AI Persona নাম দিন');
                        if (!this.form.business_hours.trim()) errors.push('বিজনেস সময় দিন');
                        if (!this.form.business_description.trim()) errors.push('বিজনেস বিবরণ দিন');
                    }

                    if (step === 3) {
                        if (!this.form.formality_level) errors.push('ফর্মালিটি বাছুন');
                        if (!this.form.emoji_usage) errors.push('ইমোজি ব্যবহার বাছুন');
                        if (!this.form.language_style) errors.push('ভাষা স্টাইল বাছুন');
                        if (!this.form.greeting_style.trim()) errors.push('গ্রিটিং স্টাইল দিন');
                    }

                    if (errors.length > 0) {
                        this.stepError = errors[0];
                        return false;
                    }
                    return true;
                },

                nextStep() {
                    if (!this.validateStep(this.currentStep)) {
                        return;
                    }
                    if (this.currentStep < this.totalSteps) {
                        this.stepError = '';
                        this.currentStep++;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                },

                prevStep() {
                    if (this.currentStep > 1) {
                        this.stepError = '';
                        this.currentStep--;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                },

                addFaq() {
                    this.faq.push({ question: '', answer: '' });
                },

                removeFaq(index) {
                    this.faq.splice(index, 1);
                },

                loadExistingData() {
                    @if(old())
                        const oldData = @json(old());
                        Object.keys(this.form).forEach(key => {
                            if (oldData[key] !== undefined) this.form[key] = oldData[key];
                        });
                        if (oldData.faq) {
                            try {
                                this.faq = typeof oldData.faq === 'string' ? JSON.parse(oldData.faq) : oldData.faq;
                                if (!Array.isArray(this.faq)) this.faq = [{ question: '', answer: '' }];
                            } catch(e) {
                                this.faq = [{ question: '', answer: '' }];
                            }
                        }
                        if (oldData.accepted_payment_methods) {
                            try {
                                let pm = typeof oldData.accepted_payment_methods === 'string' ? JSON.parse(oldData.accepted_payment_methods) : oldData.accepted_payment_methods;
                                if (Array.isArray(pm) && pm.length > 0) {
                                    this.paymentMethods = pm;
                                }
                            } catch(e) {}
                        }
                        if (oldData.delivery_areas) {
                            try {
                                let da = typeof oldData.delivery_areas === 'string' ? JSON.parse(oldData.delivery_areas) : oldData.delivery_areas;
                                if (Array.isArray(da) && da.length > 0) {
                                    this.deliveryAreas = da;
                                }
                            } catch(e) {}
                        }
                        Object.keys(oldData).forEach(key => {
                            if (key.startsWith('extra_')) {
                                this.extraFields[key.replace('extra_', '')] = oldData[key];
                            }
                        });
                    @endif
                }
            }
        }
    </script>
</body>
</html>
