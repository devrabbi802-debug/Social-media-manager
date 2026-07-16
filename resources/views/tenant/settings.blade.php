@extends('layouts.tenant')

@section('title', __('settings.title').' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50" x-data="settingsApp()">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <h1 class="text-2xl font-bold text-gray-900">@lang('settings.title')</h1>
            <p class="text-gray-600">@lang('settings.subtitle')</p>
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
            {{-- Left Column --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Tab Navigation --}}
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                    <div class="border-b border-gray-200">
                        <nav class="flex overflow-x-auto px-4 -mb-px" aria-label="Tabs">
                            <button @click="tab = 'profile'" :class="tab === 'profile' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="whitespace-nowrap py-3 px-4 border-b-2 font-medium text-sm transition">
                                @lang('settings.tab_profile')
                            </button>
                            <button @click="tab = 'password'" :class="tab === 'password' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="whitespace-nowrap py-3 px-4 border-b-2 font-medium text-sm transition">
                                @lang('settings.tab_password')
                            </button>
                            <button @click="tab = 'business'" :class="tab === 'business' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="whitespace-nowrap py-3 px-4 border-b-2 font-medium text-sm transition">
                                @lang('settings.tab_business_info')
                            </button>
                            <button @click="tab = 'tone'" :class="tab === 'tone' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="whitespace-nowrap py-3 px-4 border-b-2 font-medium text-sm transition">
                                @lang('settings.tab_tone')
                            </button>
                            <button @click="tab = 'pricing'" :class="tab === 'pricing' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="whitespace-nowrap py-3 px-4 border-b-2 font-medium text-sm transition">
                                @lang('settings.tab_pricing')
                            </button>
                            <button @click="tab = 'delivery'" :class="tab === 'delivery' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="whitespace-nowrap py-3 px-4 border-b-2 font-medium text-sm transition">
                                @lang('settings.tab_delivery_payment')
                            </button>
                            <button @click="tab = 'faq'" :class="tab === 'faq' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="whitespace-nowrap py-3 px-4 border-b-2 font-medium text-sm transition">
                                @lang('settings.tab_faq')
                            </button>
                            <button @click="tab = 'escalation'" :class="tab === 'escalation' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="whitespace-nowrap py-3 px-4 border-b-2 font-medium text-sm transition">
                                @lang('settings.tab_escalation')
                            </button>
                        </nav>
                    </div>

                    <div class="p-6">
                        {{-- Profile Tab --}}
                        <div x-show="tab === 'profile'" x-transition>
                            <h2 class="text-lg font-bold text-gray-900 mb-6">@lang('settings.profile_info')</h2>
                            <form method="POST" action="{{ route('settings.profile.update') }}">
                                @csrf
                                @method('PUT')
                                <div class="grid md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">@lang('common.name')</label>
                                        <input type="text" name="name" value="{{ old('name', Auth::user()->name) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                                        @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">@lang('common.email')</label>
                                        <input type="email" name="email" value="{{ old('email', Auth::user()->email) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                                        @error('email') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">@lang('common.phone')</label>
                                        <input type="text" name="phone" value="{{ old('phone', Auth::user()->phone) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                                        @error('phone') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">@lang('common.company')</label>
                                        <input type="text" name="company" value="{{ old('company', Auth::user()->company) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                                        @error('company') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                                <div class="mt-6 flex justify-end">
                                    <button type="submit" class="bg-purple-600 text-white px-6 py-2.5 rounded-xl font-medium hover:bg-purple-700 transition">@lang('common.save')</button>
                                </div>
                            </form>
                        </div>

                        {{-- Password Tab --}}
                        <div x-show="tab === 'password'" x-transition>
                            <h2 class="text-lg font-bold text-gray-900 mb-6">@lang('settings.change_password')</h2>
                            <form method="POST" action="{{ route('settings.password.update') }}">
                                @csrf
                                @method('PUT')
                                <div class="space-y-4 max-w-md">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">@lang('settings.current_password')</label>
                                        <input type="password" name="current_password" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                                        @error('current_password') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">@lang('settings.new_password')</label>
                                        <input type="password" name="password" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                                        @error('password') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">@lang('settings.confirm_password')</label>
                                        <input type="password" name="password_confirmation" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                                    </div>
                                </div>
                                <div class="mt-6 flex justify-end">
                                    <button type="submit" class="bg-purple-600 text-white px-6 py-2.5 rounded-xl font-medium hover:bg-purple-700 transition">@lang('settings.update_password')</button>
                                </div>
                            </form>
                        </div>

                        {{-- Business Info Tab --}}
                        <div x-show="tab === 'business'" x-transition>
                            <h2 class="text-lg font-bold text-gray-900 mb-2">@lang('settings.tab_business_info')</h2>
                            <p class="text-sm text-gray-500 mb-6">@lang('settings.business_settings_subtitle')</p>
                            <form method="POST" action="{{ route('settings.business-info.update') }}">
                                @csrf
                                @method('PUT')
                                <div class="space-y-5">
                                    <div class="grid md:grid-cols-2 gap-5">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">@lang('settings.business_name')</label>
                                            <input type="text" name="business_name" value="{{ old('business_name', $businessSetting->business_name ?? '') }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                                            <p class="text-xs text-gray-400 mt-1">@lang('settings.business_name_hint')</p>
                                            @error('business_name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">@lang('settings.persona_name')</label>
                                            <input type="text" name="persona_name" value="{{ old('persona_name', $businessSetting->persona_name ?? '') }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                                            <p class="text-xs text-gray-400 mt-1">@lang('settings.persona_name_hint')</p>
                                            @error('persona_name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                                        </div>
                                    </div>
                                    <div class="grid md:grid-cols-2 gap-5">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">@lang('settings.category')</label>
                                            <select name="category_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                                                <option value="">@lang('settings.select_category')</option>
                                                @php $currentCatId = $businessSetting->category_id ?? ''; @endphp
                                                @foreach($categories as $cat)
                                                    <option value="{{ $cat->id }}" {{ $currentCatId == $cat->id ? 'selected' : '' }}>{{ $cat->icon }} {{ $cat->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('category_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">@lang('settings.sub_category')</label>
                                            <input type="text" name="sub_category" value="{{ old('sub_category', $businessSetting->sub_category ?? '') }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                                            <p class="text-xs text-gray-400 mt-1">@lang('settings.sub_category_hint')</p>
                                        </div>
                                    </div>
                                    <div class="grid md:grid-cols-2 gap-5">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">@lang('settings.business_hours')</label>
                                            <input type="text" name="business_hours" value="{{ old('business_hours', $businessSetting->business_hours ?? '') }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                                            <p class="text-xs text-gray-400 mt-1">@lang('settings.business_hours_hint')</p>
                                            @error('business_hours') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">@lang('settings.off_hours_message')</label>
                                            <input type="text" name="off_hours_message" value="{{ old('off_hours_message', $businessSetting->off_hours_message ?? '') }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                                            <p class="text-xs text-gray-400 mt-1">@lang('settings.off_hours_message_hint')</p>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">@lang('settings.business_description')</label>
                                        <textarea name="business_description" rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">{{ old('business_description', $businessSetting->business_description ?? '') }}</textarea>
                                        <p class="text-xs text-gray-400 mt-1">@lang('settings.business_description_hint')</p>
                                        @error('business_description') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                                <div class="mt-6 flex justify-end">
                                    <button type="submit" class="bg-purple-600 text-white px-6 py-2.5 rounded-xl font-medium hover:bg-purple-700 transition">@lang('common.save')</button>
                                </div>
                            </form>
                        </div>

                        {{-- Tone & Communication Tab --}}
                        <div x-show="tab === 'tone'" x-transition>
                            <h2 class="text-lg font-bold text-gray-900 mb-6">@lang('settings.tab_tone')</h2>
                            <form method="POST" action="{{ route('settings.tone.update') }}">
                                @csrf
                                @method('PUT')
                                <div class="space-y-5">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-3">@lang('settings.formality_level')</label>
                                        <div class="flex gap-4">
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="radio" name="formality_level" value="formal" {{ ($businessSetting->formality_level ?? '') === 'formal' ? 'checked' : '' }} class="text-purple-600 focus:ring-purple-500">
                                                <span class="text-sm text-gray-700">@lang('settings.formal')</span>
                                            </label>
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="radio" name="formality_level" value="casual" {{ ($businessSetting->formality_level ?? '') === 'casual' ? 'checked' : '' }} class="text-purple-600 focus:ring-purple-500">
                                                <span class="text-sm text-gray-700">@lang('settings.casual')</span>
                                            </label>
                                        </div>
                                        @error('formality_level') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-3">@lang('settings.emoji_usage')</label>
                                        <div class="flex gap-4">
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="radio" name="emoji_usage" value="never" {{ ($businessSetting->emoji_usage ?? '') === 'never' ? 'checked' : '' }} class="text-purple-600 focus:ring-purple-500">
                                                <span class="text-sm text-gray-700">@lang('settings.emoji_never')</span>
                                            </label>
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="radio" name="emoji_usage" value="sometimes" {{ ($businessSetting->emoji_usage ?? '') === 'sometimes' ? 'checked' : '' }} class="text-purple-600 focus:ring-purple-500">
                                                <span class="text-sm text-gray-700">@lang('settings.emoji_sometimes')</span>
                                            </label>
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="radio" name="emoji_usage" value="often" {{ ($businessSetting->emoji_usage ?? '') === 'often' ? 'checked' : '' }} class="text-purple-600 focus:ring-purple-500">
                                                <span class="text-sm text-gray-700">@lang('settings.emoji_often')</span>
                                            </label>
                                        </div>
                                        @error('emoji_usage') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-3">@lang('settings.language_style')</label>
                                        <div class="flex gap-4">
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="radio" name="language_style" value="shuddho_bangla" {{ ($businessSetting->language_style ?? '') === 'shuddho_bangla' ? 'checked' : '' }} class="text-purple-600 focus:ring-purple-500">
                                                <span class="text-sm text-gray-700">@lang('settings.lang_shuddho')</span>
                                            </label>
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="radio" name="language_style" value="anjonio" {{ ($businessSetting->language_style ?? '') === 'anjonio' ? 'checked' : '' }} class="text-purple-600 focus:ring-purple-500">
                                                <span class="text-sm text-gray-700">@lang('settings.lang_anjonio')</span>
                                            </label>
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="radio" name="language_style" value="banglish" {{ ($businessSetting->language_style ?? '') === 'banglish' ? 'checked' : '' }} class="text-purple-600 focus:ring-purple-500">
                                                <span class="text-sm text-gray-700">@lang('settings.lang_banglish')</span>
                                            </label>
                                        </div>
                                        @error('language_style') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">@lang('settings.greeting_style')</label>
                                        <input type="text" name="greeting_style" value="{{ old('greeting_style', $businessSetting->greeting_style ?? '') }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition" placeholder="{{ __('settings.greeting_hint') }}">
                                        <p class="text-xs text-gray-400 mt-1">@lang('settings.greeting_hint')</p>
                                        @error('greeting_style') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                                <div class="mt-6 flex justify-end">
                                    <button type="submit" class="bg-purple-600 text-white px-6 py-2.5 rounded-xl font-medium hover:bg-purple-700 transition">@lang('common.save')</button>
                                </div>
                            </form>
                        </div>

                        {{-- Pricing Tab --}}
                        <div x-show="tab === 'pricing'" x-transition>
                            <h2 class="text-lg font-bold text-gray-900 mb-6">@lang('settings.tab_pricing')</h2>
                            <form method="POST" action="{{ route('settings.pricing.update') }}">
                                @csrf
                                @method('PUT')
                                <div class="space-y-5">
                                    <div class="flex items-center justify-between py-3">
                                        <div>
                                            <p class="font-medium text-gray-900">@lang('settings.price_negotiation')</p>
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="price_negotiation" value="1" {{ ($businessSetting->price_negotiation ?? false) ? 'checked' : '' }} x-model="priceNegotiation" class="sr-only peer">
                                            <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-purple-600"></div>
                                        </label>
                                    </div>
                                    <div x-show="priceNegotiation" x-transition>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">@lang('settings.negotiation_limit')</label>
                                        <input type="number" name="negotiation_limit" value="{{ old('negotiation_limit', $businessSetting->negotiation_limit ?? 0) }}" min="0" max="100" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition" placeholder="যেমন: 15">
                                        <p class="text-xs text-gray-400 mt-1">@lang('settings.negotiation_limit_hint')</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">@lang('settings.bulk_discount_rule')</label>
                                        <input type="text" name="bulk_discount_rule" value="{{ old('bulk_discount_rule', $businessSetting->bulk_discount_rule ?? '') }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                                        <p class="text-xs text-gray-400 mt-1">@lang('settings.bulk_discount_hint')</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">@lang('settings.current_promo')</label>
                                        <input type="text" name="current_promo" value="{{ old('current_promo', $businessSetting->current_promo ?? '') }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                                        <p class="text-xs text-gray-400 mt-1">@lang('settings.promo_hint')</p>
                                    </div>
                                </div>
                                <div class="mt-6 flex justify-end">
                                    <button type="submit" class="bg-purple-600 text-white px-6 py-2.5 rounded-xl font-medium hover:bg-purple-700 transition">@lang('common.save')</button>
                                </div>
                            </form>
                        </div>

                        {{-- Delivery & Payment Tab --}}
                        <div x-show="tab === 'delivery'" x-transition>
                            <h2 class="text-lg font-bold text-gray-900 mb-2">@lang('settings.tab_delivery_payment')</h2>
                            <p class="text-sm text-gray-500 mb-6">@lang('settings.business_settings_subtitle')</p>

                            <form method="POST" action="{{ route('settings.business.update') }}" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                {{-- Logo Section --}}
                                <h3 class="font-semibold text-gray-900 border-b pb-2 mb-4">@lang('settings.company_logo')</h3>
                                <div class="mb-6">
                                    @if($businessSetting->logo_path)
                                        <div class="mb-4">
                                            <img src="{{ $businessSetting->getLogoUrl() }}" alt="Company Logo" class="h-20 rounded-xl object-contain border border-gray-200">
                                        </div>
                                    @endif
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">@lang('settings.upload_logo')</label>
                                        <input type="file" name="logo" accept="image/*" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition text-sm">
                                        <p class="text-xs text-gray-400 mt-1">PNG, JPG, SVG (সর্বোচ্চ ২MB)</p>
                                    </div>
                                </div>

                                {{-- Delivery Section --}}
                                <h3 class="font-semibold text-gray-900 border-b pb-2 mb-4">@lang('settings.delivery')</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">@lang('settings.delivery_areas')</label>
                                        <p class="text-xs text-gray-400 mb-3">@lang('settings.delivery_area_hint')</p>
                                        <div class="space-y-3">
                                            <template x-for="(area, index) in deliveryAreas" :key="index">
                                                <div class="flex items-start gap-3 bg-gray-50 rounded-xl p-3">
                                                    <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                        <input type="text" x-model="area.name" class="w-full px-3 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm" placeholder="এরিয়ার নাম">
                                                        <input type="text" x-model="area.price" class="w-full px-3 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm" placeholder="চার্জ">
                                                    </div>
                                                    <button type="button" @click="deliveryAreas.splice(index, 1)" x-show="deliveryAreas.length > 1" class="mt-1 text-red-400 hover:text-red-600 transition">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </div>
                                            </template>
                                        </div>
                                        <button type="button" @click="deliveryAreas.push({name: '', price: ''})" class="mt-3 flex items-center space-x-2 text-purple-600 hover:text-purple-700 text-sm font-medium transition">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                            <span>@lang('settings.add_delivery_area')</span>
                                        </button>
                                        <input type="hidden" name="delivery_areas" :value="JSON.stringify(deliveryAreas.filter(a => a.name.trim()))">
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">@lang('settings.delivery_time')</label>
                                            <input type="text" name="delivery_time" value="{{ old('delivery_time', $businessSetting->delivery_time ?? '') }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition" placeholder="যেমন: ৩-৫ দিন">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">@lang('settings.delivery_partner')</label>
                                            <input type="text" name="delivery_partner" value="{{ old('delivery_partner', $businessSetting->delivery_partner ?? '') }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition" placeholder="যেমন: পাঠাও/স্টেডফাস্ট">
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between py-3">
                                        <div><p class="font-medium text-gray-900">@lang('settings.cod_available')</p></div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="cod_available" value="1" {{ ($businessSetting->cod_available ?? true) ? 'checked' : '' }} class="sr-only peer">
                                            <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-purple-600"></div>
                                        </label>
                                    </div>
                                </div>

                                {{-- Payment Section --}}
                                <h3 class="font-semibold text-gray-900 border-b pb-2 pt-6 mb-4">@lang('settings.payment')</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">@lang('settings.accepted_payment_methods')</label>
                                        <p class="text-xs text-gray-400 mb-3">@lang('settings.payment_method_hint')</p>
                                        <div class="space-y-3">
                                            <template x-for="(method, index) in paymentMethods" :key="index">
                                                <div class="flex items-start gap-3 bg-gray-50 rounded-xl p-3">
                                                    <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                        <input type="text" x-model="method.name" class="w-full px-3 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm" placeholder="মেথডের নাম">
                                                        <input type="text" x-model="method.details" class="w-full px-3 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm" placeholder="বিস্তারিত">
                                                    </div>
                                                    <button type="button" @click="paymentMethods.splice(index, 1)" x-show="paymentMethods.length > 1" class="mt-1 text-red-400 hover:text-red-600 transition">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </div>
                                            </template>
                                        </div>
                                        <button type="button" @click="paymentMethods.push({name: '', details: ''})" class="mt-3 flex items-center space-x-2 text-purple-600 hover:text-purple-700 text-sm font-medium transition">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                            <span>@lang('settings.add_payment_method')</span>
                                        </button>
                                        <input type="hidden" name="accepted_payment_methods" :value="JSON.stringify(paymentMethods.filter(m => m.name.trim()))">
                                    </div>
                                    <div class="flex items-center justify-between py-3">
                                        <div><p class="font-medium text-gray-900">@lang('settings.advance_payment')</p></div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="advance_payment_required" value="1" {{ ($businessSetting->advance_payment_required ?? false) ? 'checked' : '' }} x-model="advancePayment" class="sr-only peer">
                                            <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-purple-600"></div>
                                        </label>
                                    </div>
                                    <div x-show="advancePayment" x-transition>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">@lang('settings.advance_payment_percent')</label>
                                        <input type="number" name="advance_payment_percent" value="{{ old('advance_payment_percent', $businessSetting->advance_payment_percent ?? 0) }}" min="0" max="100" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition" placeholder="যেমন: 50">
                                    </div>
                                    <div class="flex items-center justify-between py-3">
                                        <div>
                                            <p class="font-medium text-gray-900">@lang('settings.advance_for_outside_dhaka')</p>
                                            <p class="text-xs text-gray-400">@lang('settings.advance_for_outside_dhaka_hint')</p>
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="advance_for_outside_dhaka" value="1" {{ ($businessSetting->advance_for_outside_dhaka ?? false) ? 'checked' : '' }} class="sr-only peer">
                                            <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-purple-600"></div>
                                        </label>
                                    </div>
                                </div>

                                {{-- Policies Section --}}
                                <h3 class="font-semibold text-gray-900 border-b pb-2 pt-6 mb-4">@lang('settings.policies')</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">@lang('settings.refund_policy')</label>
                                        <textarea name="refund_policy" rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition" placeholder="যেমন: পণ্য হাতে পেয়ে ৩ দিনের মধ্যে রিফান্ড সম্ভব...">{{ old('refund_policy', $businessSetting->refund_policy ?? '') }}</textarea>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">@lang('settings.exchange_policy')</label>
                                        <textarea name="exchange_policy" rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition" placeholder="যেমন: ৭ দিনের মধ্যে এক্সচেঞ্জ সম্ভব...">{{ old('exchange_policy', $businessSetting->exchange_policy ?? '') }}</textarea>
                                    </div>
                                </div>

                                {{-- Order Process Section --}}
                                <h3 class="font-semibold text-gray-900 border-b pb-2 pt-6 mb-4">@lang('settings.order_process')</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">@lang('settings.order_process_message')</label>
                                        <p class="text-xs text-gray-400 mb-2">@lang('settings.order_process_hint')</p>
                                        <textarea name="order_process_message" rows="8" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition font-mono text-sm" placeholder="{{ __('settings.order_process_placeholder') }}">{{ old('order_process_message', $businessSetting->order_process_message ?? '') }}</textarea>
                                    </div>
                                </div>

                                <div class="mt-6 flex justify-end">
                                    <button type="submit" class="bg-purple-600 text-white px-6 py-2.5 rounded-xl font-medium hover:bg-purple-700 transition">@lang('common.save')</button>
                                </div>
                            </form>
                        </div>

                        {{-- FAQ Tab --}}
                        <div x-show="tab === 'faq'" x-transition>
                            <h2 class="text-lg font-bold text-gray-900 mb-2">@lang('settings.faq_title')</h2>
                            <p class="text-sm text-gray-500 mb-6">@lang('settings.faq_subtitle')</p>
                            <form method="POST" action="{{ route('settings.faq.update') }}">
                                @csrf
                                @method('PUT')
                                <div class="space-y-4">
                                    <template x-for="(item, index) in faqItems" :key="index">
                                        <div class="bg-gray-50 rounded-xl p-4 space-y-3">
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm font-medium text-gray-500" x-text="'#' + (index + 1)"></span>
                                                <button type="button" @click="faqItems.splice(index, 1)" x-show="faqItems.length > 1" class="text-red-400 hover:text-red-600 transition">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </div>
                                            <div>
                                                <input type="text" x-model="item.question" class="w-full px-3 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm" placeholder="{{ __('settings.question_placeholder') }}">
                                            </div>
                                            <div>
                                                <textarea x-model="item.answer" rows="2" class="w-full px-3 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm" placeholder="{{ __('settings.answer_placeholder') }}"></textarea>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                <button type="button" @click="faqItems.push({question: '', answer: ''})" class="mt-4 flex items-center space-x-2 text-purple-600 hover:text-purple-700 text-sm font-medium transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                    <span>@lang('settings.add_faq')</span>
                                </button>
                                <input type="hidden" name="faq" :value="JSON.stringify(faqItems.filter(f => f.question.trim()))">
                                <div class="mt-6 flex justify-end">
                                    <button type="submit" class="bg-purple-600 text-white px-6 py-2.5 rounded-xl font-medium hover:bg-purple-700 transition">@lang('common.save')</button>
                                </div>
                            </form>
                        </div>

                        {{-- Escalation Tab --}}
                        <div x-show="tab === 'escalation'" x-transition>
                            <h2 class="text-lg font-bold text-gray-900 mb-2">@lang('settings.escalation_title')</h2>
                            <p class="text-sm text-gray-500 mb-6">@lang('settings.escalation_subtitle')</p>
                            <form method="POST" action="{{ route('settings.escalation.update') }}">
                                @csrf
                                @method('PUT')
                                <div class="space-y-5">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">@lang('settings.escalation_keywords')</label>
                                        <input type="text" name="custom_escalation_keywords" value="{{ old('custom_escalation_keywords', $businessSetting->custom_escalation_keywords ?? '') }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition" placeholder="অভিযোগ, সমস্যা, রিফান্ড">
                                        <p class="text-xs text-gray-400 mt-1">@lang('settings.escalation_keywords_hint')</p>
                                        @error('custom_escalation_keywords') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">@lang('settings.escalation_contact')</label>
                                        <input type="text" name="escalation_contact" value="{{ old('escalation_contact', $businessSetting->escalation_contact ?? '') }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition" placeholder="01XXXXXXXXX বা email@example.com">
                                        <p class="text-xs text-gray-400 mt-1">@lang('settings.escalation_contact_hint')</p>
                                        @error('escalation_contact') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                                <div class="mt-6 flex justify-end">
                                    <button type="submit" class="bg-purple-600 text-white px-6 py-2.5 rounded-xl font-medium hover:bg-purple-700 transition">@lang('common.save')</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column --}}
            <div class="space-y-6">
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">@lang('settings.account_info')</h2>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-500">@lang('settings.username')</span>
                            <span class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</span>
                        </div>
                        <div class="flex items-center justify-between py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-500">@lang('common.email')</span>
                            <span class="text-sm font-medium text-gray-900">{{ Auth::user()->email }}</span>
                        </div>
                        <div class="flex items-center justify-between py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-500">@lang('settings.email_verification')</span>
                            @if(Auth::user()->email_verified_at)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">@lang('settings.verified')</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">@lang('settings.unverified')</span>
                            @endif
                        </div>
                        <div class="flex items-center justify-between py-2">
                            <span class="text-sm text-gray-500">@lang('settings.member_since')</span>
                            <span class="text-sm font-medium text-gray-900">{{ Auth::user()->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-red-200">
                    <h2 class="text-lg font-bold text-red-600 mb-4">@lang('settings.danger_zone')</h2>
                    <p class="text-sm text-gray-600 mb-4">@lang('settings.delete_account_warning')</p>
                    <button type="button" class="w-full border border-red-600 text-red-600 px-4 py-2 rounded-xl font-medium hover:bg-red-50 transition">
                        @lang('settings.delete_account')
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function settingsApp() {
    return {
        tab: 'business',
        advancePayment: {{ ($businessSetting->advance_payment_required ?? false) ? 'true' : 'false' }},
        priceNegotiation: {{ ($businessSetting->price_negotiation ?? false) ? 'true' : 'false' }},
        paymentMethods: <?php echo json_encode($businessSetting->accepted_payment_methods ?? [['name' => '', 'details' => '']]); ?>,
        deliveryAreas: <?php echo json_encode($businessSetting->delivery_areas ?? [['name' => 'Inside Dhaka', 'price' => ''], ['name' => 'Outside Dhaka', 'price' => '']]); ?>,
        faqItems: <?php echo json_encode(!empty($businessSetting->faq) ? $businessSetting->faq : [['question' => '', 'answer' => '']]); ?>,

        init() {
            if (!Array.isArray(this.paymentMethods) || this.paymentMethods.length === 0) {
                this.paymentMethods = [{ name: '', details: '' }];
            }
            if (!Array.isArray(this.deliveryAreas) || this.deliveryAreas.length === 0) {
                this.deliveryAreas = [{ name: 'Inside Dhaka', price: '' }, { name: 'Outside Dhaka', price: '' }];
            }
            if (!Array.isArray(this.faqItems) || this.faqItems.length === 0) {
                this.faqItems = [{ question: '', answer: '' }];
            }
        }
    }
}
</script>
@endsection
