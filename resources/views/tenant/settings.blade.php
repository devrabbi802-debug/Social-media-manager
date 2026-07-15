@extends('layouts.tenant')

@section('title', __('settings.title').' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50" x-data="businessSettings()">
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
                {{-- Profile Settings --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-6">@lang('settings.profile_info')</h2>
                    <form method="POST" action="{{ url('/settings/profile') }}">
                        @csrf
                        @method('PUT')

                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">@lang('common.name')</label>
                                <input type="text" name="name" value="{{ old('name', Auth::user()->name) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                                @error('name')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">@lang('common.email')</label>
                                <input type="email" name="email" value="{{ old('email', Auth::user()->email) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                                @error('email')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">@lang('common.phone')</label>
                                <input type="text" name="phone" value="{{ old('phone', Auth::user()->phone) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                                @error('phone')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">@lang('common.company')</label>
                                <input type="text" name="company" value="{{ old('company', Auth::user()->company) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                                @error('company')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="bg-purple-600 text-white px-6 py-2.5 rounded-xl font-medium hover:bg-purple-700 transition">
                                @lang('common.save')
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Password Change --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-6">@lang('settings.change_password')</h2>
                    <form method="POST" action="{{ url('/settings/password') }}">
                        @csrf
                        @method('PUT')

                        <div class="space-y-4 max-w-md">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">@lang('settings.current_password')</label>
                                <input type="password" name="current_password" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                                @error('current_password')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">@lang('settings.new_password')</label>
                                <input type="password" name="password" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                                @error('password')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">@lang('settings.confirm_password')</label>
                                <input type="password" name="password_confirmation" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="bg-purple-600 text-white px-6 py-2.5 rounded-xl font-medium hover:bg-purple-700 transition">
                                @lang('settings.update_password')
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Business Settings --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-2">@lang('settings.business_settings')</h2>
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
                                <input type="file" name="logo" accept="image/*"
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition text-sm">
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
                                    <span>@lang('settings.add_delivery_area')</span>
                                </button>

                                <input type="hidden" name="delivery_areas" :value="JSON.stringify(deliveryAreas.filter(a => a.name.trim()))">
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">@lang('settings.delivery_time')</label>
                                    <input type="text" name="delivery_time"
                                           value="{{ old('delivery_time', $businessSetting->delivery_time ?? '') }}"
                                           class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                                           placeholder="যেমন: ৩-৫ দিন">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">@lang('settings.delivery_partner')</label>
                                    <input type="text" name="delivery_partner"
                                           value="{{ old('delivery_partner', $businessSetting->delivery_partner ?? '') }}"
                                           class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                                           placeholder="যেমন: পাঠাও/স্টেডফাস্ট">
                                </div>
                            </div>

                            <div class="flex items-center justify-between py-3">
                                <div>
                                    <p class="font-medium text-gray-900">@lang('settings.cod_available')</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="cod_available" value="1"
                                           {{ ($businessSetting->cod_available ?? true) ? 'checked' : '' }}
                                           class="sr-only peer">
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
                                    <span>@lang('settings.add_payment_method')</span>
                                </button>

                                <input type="hidden" name="accepted_payment_methods" :value="JSON.stringify(paymentMethods.filter(m => m.name.trim()))">
                            </div>

                            <div class="flex items-center justify-between py-3">
                                <div>
                                    <p class="font-medium text-gray-900">@lang('settings.advance_payment')</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="advance_payment_required" value="1"
                                           {{ ($businessSetting->advance_payment_required ?? false) ? 'checked' : '' }}
                                           x-model="advancePayment"
                                           class="sr-only peer">
                                    <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-purple-600"></div>
                                </label>
                            </div>

                            <div x-show="advancePayment" x-transition>
                                <label class="block text-sm font-medium text-gray-700 mb-1">@lang('settings.advance_payment_percent')</label>
                                <input type="number" name="advance_payment_percent"
                                       value="{{ old('advance_payment_percent', $businessSetting->advance_payment_percent ?? 0) }}"
                                       min="0" max="100"
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                                       placeholder="যেমন: 50">
                            </div>

                            <div class="flex items-center justify-between py-3">
                                <div>
                                    <p class="font-medium text-gray-900">@lang('settings.advance_for_outside_dhaka')</p>
                                    <p class="text-xs text-gray-400">@lang('settings.advance_for_outside_dhaka_hint')</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="advance_for_outside_dhaka" value="1"
                                           {{ ($businessSetting->advance_for_outside_dhaka ?? false) ? 'checked' : '' }}
                                           class="sr-only peer">
                                    <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-purple-600"></div>
                                </label>
                            </div>
                        </div>

                        {{-- Policies Section --}}
                        <h3 class="font-semibold text-gray-900 border-b pb-2 pt-6 mb-4">@lang('settings.policies')</h3>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">@lang('settings.refund_policy')</label>
                                <textarea name="refund_policy" rows="3"
                                          class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                                          placeholder="যেমন: পণ্য হাতে পেয়ে ৩ দিনের মধ্যে রিফান্ড সম্ভব...">{{ old('refund_policy', $businessSetting->refund_policy ?? '') }}</textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">@lang('settings.exchange_policy')</label>
                                <textarea name="exchange_policy" rows="3"
                                          class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                                          placeholder="যেমন: ৭ দিনের মধ্যে এক্সচেঞ্জ সম্ভব, পণ্য অব্যবহৃত হতে হবে...">{{ old('exchange_policy', $businessSetting->exchange_policy ?? '') }}</textarea>
                            </div>
                        </div>

                        {{-- Order Process Section --}}
                        <h3 class="font-semibold text-gray-900 border-b pb-2 pt-6 mb-4">@lang('settings.order_process')</h3>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">@lang('settings.order_process_message')</label>
                                <p class="text-xs text-gray-400 mb-2">@lang('settings.order_process_hint')</p>
                                <textarea name="order_process_message" rows="8"
                                          class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition font-mono text-sm"
                                          placeholder="{{ __('settings.order_process_placeholder') }}">{{ old('order_process_message', $businessSetting->order_process_message ?? '') }}</textarea>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="bg-purple-600 text-white px-6 py-2.5 rounded-xl font-medium hover:bg-purple-700 transition">
                                @lang('common.save')
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Right Column --}}
            <div class="space-y-6">
                {{-- Account Info --}}
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

                {{-- Danger Zone --}}
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
function businessSettings() {
    return {
        advancePayment: {{ ($businessSetting->advance_payment_required ?? false) ? 'true' : 'false' }},
        paymentMethods: @json($businessSetting->accepted_payment_methods ?? [{ 'name' => '', 'details' => '' }]),
        deliveryAreas: @json($businessSetting->delivery_areas ?? [{ 'name' => 'Inside Dhaka', 'price' => '' }, { 'name' => 'Outside Dhaka', 'price' => '' }]),

        init() {
            if (!Array.isArray(this.paymentMethods) || this.paymentMethods.length === 0) {
                this.paymentMethods = [{ name: '', details: '' }];
            }
            if (!Array.isArray(this.deliveryAreas) || this.deliveryAreas.length === 0) {
                this.deliveryAreas = [{ name: 'Inside Dhaka', price: '' }, { name: 'Outside Dhaka', price: '' }];
            }
        }
    }
}
</script>
@endsection
