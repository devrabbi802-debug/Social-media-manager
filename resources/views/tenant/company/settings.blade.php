@extends('layouts.tenant')

@section('title', __('company_settings.title').' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50" x-data="{ tab: 'general' }">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">@lang('company_settings.title')</h1>
                    <p class="text-gray-600">@lang('company_settings.subtitle')</p>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="max-w-7xl mx-auto mt-4 px-4">
            <div class="p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm">{{ session('success') }}</div>
        </div>
    @endif

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Tabs --}}
        <div class="border-b border-gray-200 mb-6">
            <nav class="flex space-x-8">
                <button @click="tab = 'general'" :class="tab === 'general' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition">@lang('company_settings.tab_general')</button>
                <button @click="tab = 'warehouse'" :class="tab === 'warehouse' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition">@lang('company_settings.tab_warehouse')</button>
                <button @click="tab = 'financial'" :class="tab === 'financial' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition">@lang('company_settings.tab_financial')</button>
                <button @click="tab = 'payment'" :class="tab === 'payment' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition">@lang('company_settings.tab_payment')</button>
                <button @click="tab = 'modules'" :class="tab === 'modules' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition">@lang('company_settings.tab_modules')</button>
            </nav>
        </div>

        <form method="POST" action="{{ route('company.settings.update') }}">
            @csrf
            @method('PUT')

            {{-- General Info --}}
            <div x-show="tab === 'general'" x-transition class="space-y-6">
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">@lang('company_settings.general_info')</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">@lang('company_settings.business_name')</label>
                            <input type="text" name="business_name" value="{{ old('business_name', $settings->business_name) }}" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">@lang('company_settings.store_name')</label>
                            <input type="text" name="store_name" value="{{ old('store_name', $settings->store_name) }}" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">@lang('company_settings.store_phone')</label>
                            <input type="text" name="store_phone" value="{{ old('store_phone', $settings->store_phone) }}" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">@lang('company_settings.store_email')</label>
                            <input type="email" name="store_email" value="{{ old('store_email', $settings->store_email) }}" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">@lang('company_settings.store_address')</label>
                            <input type="text" name="store_address" value="{{ old('store_address', $settings->store_address) }}" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        </div>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-2.5 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition">@lang('common.save')</button>
                </div>
            </div>

            {{-- Warehouse & Stock --}}
            <div x-show="tab === 'warehouse'" x-transition class="space-y-6">
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h3 class="text-lg font-bold text-gray-900 mb-2">@lang('company_settings.warehouse_title')</h3>
                    <p class="text-sm text-gray-500 mb-4">@lang('company_settings.warehouse_desc')</p>
                    <div class="max-w-md">
                        <label class="block text-sm font-medium text-gray-700 mb-1">@lang('company_settings.default_warehouse')</label>
                        <select name="default_warehouse_id" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            <option value="">@lang('company_settings.no_warehouse')</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ $settings->default_warehouse_id == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-2.5 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition">@lang('common.save')</button>
                </div>
            </div>

            {{-- Financial --}}
            <div x-show="tab === 'financial'" x-transition class="space-y-6">
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">@lang('company_settings.financial_title')</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">@lang('company_settings.currency')</label>
                            <input type="text" name="currency" value="{{ old('currency', $settings->currency) }}" maxlength="10" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">@lang('company_settings.currency_symbol')</label>
                            <input type="text" name="currency_symbol" value="{{ old('currency_symbol', $settings->currency_symbol) }}" maxlength="10" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">@lang('company_settings.tax_rate') (%)</label>
                            <input type="number" step="0.01" min="0" max="100" name="tax_rate" value="{{ old('tax_rate', $settings->tax_rate) }}" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">@lang('company_settings.tax_type')</label>
                            <select name="tax_type" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                <option value="inclusive" {{ $settings->tax_type === 'inclusive' ? 'selected' : '' }}>Inclusive</option>
                                <option value="exclusive" {{ $settings->tax_type === 'exclusive' ? 'selected' : '' }}>Exclusive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-2.5 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition">@lang('common.save')</button>
                </div>
            </div>

            {{-- Payment Methods --}}
            <div x-show="tab === 'payment'" x-transition class="space-y-6">
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h3 class="text-lg font-bold text-gray-900 mb-2">@lang('company_settings.payment_methods_title')</h3>
                    <p class="text-sm text-gray-500 mb-4">@lang('company_settings.payment_methods_desc')</p>

                    <div class="space-y-2 mb-6">
                        @forelse($paymentAccounts as $method)
                            <label class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition cursor-pointer">
                                <input type="checkbox" name="payment_methods[]" value="{{ $method['code'] }}"
                                    {{ in_array($method['code'], $settings->payment_methods ?? []) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                <div>
                                    <span class="text-sm font-medium text-gray-900">{{ $method['name'] }}</span>
                                    <span class="text-xs text-gray-400 ml-1">({{ $method['code'] }})</span>
                                </div>
                            </label>
                        @empty
                            <p class="text-sm text-gray-400">@lang('company_settings.no_payment_accounts')</p>
                        @endforelse
                    </div>

                    <div class="max-w-sm">
                        <label class="block text-sm font-medium text-gray-700 mb-1">@lang('company_settings.default_payment_method')</label>
                        <select name="default_payment_method" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            @foreach($paymentAccounts as $method)
                                <option value="{{ $method['code'] }}" {{ ($settings->default_payment_method ?? '') === $method['code'] ? 'selected' : '' }}>{{ $method['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-2.5 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition">@lang('common.save')</button>
                </div>
            </div>
        </form>

        {{-- Module Settings Links --}}
        <div x-show="tab === 'modules'" x-transition class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                {{-- POS --}}
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:border-purple-200 transition">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h18v18H3V3zm3 3v2h12V6H6zm0 4v2h8v-2H6zm0 4v2h12v-2H6zm0 4v2h5v-2H6z"/></svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900">@lang('sidebar.pos')</h4>
                            <p class="text-xs text-gray-500">@lang('company_settings.pos_desc')</p>
                        </div>
                    </div>
                    <a href="{{ route('pos.settings') }}" class="block text-center px-4 py-2 bg-gray-100 text-gray-700 rounded-xl text-sm font-medium hover:bg-purple-50 hover:text-purple-700 transition">@lang('common.edit')</a>
                </div>

                {{-- Accounting --}}
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:border-purple-200 transition">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900">@lang('sidebar.accounting')</h4>
                            <p class="text-xs text-gray-500">@lang('company_settings.accounting_desc')</p>
                        </div>
                    </div>
                    <a href="{{ route('accounting.settings.index') }}" class="block text-center px-4 py-2 bg-gray-100 text-gray-700 rounded-xl text-sm font-medium hover:bg-purple-50 hover:text-purple-700 transition">@lang('common.edit')</a>
                </div>

                {{-- Purchase --}}
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:border-purple-200 transition">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900">@lang('sidebar.purchase')</h4>
                            <p class="text-xs text-gray-500">@lang('company_settings.purchase_desc')</p>
                        </div>
                    </div>
                    <a href="{{ route('purchase.settings') }}" class="block text-center px-4 py-2 bg-gray-100 text-gray-700 rounded-xl text-sm font-medium hover:bg-purple-50 hover:text-purple-700 transition">@lang('common.edit')</a>
                </div>

                {{-- Storefront --}}
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:border-purple-200 transition">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-pink-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900">@lang('sidebar.web_setup')</h4>
                            <p class="text-xs text-gray-500">@lang('company_settings.storefront_desc')</p>
                        </div>
                    </div>
                    <a href="{{ route('storefront-settings.index') }}" class="block text-center px-4 py-2 bg-gray-100 text-gray-700 rounded-xl text-sm font-medium hover:bg-purple-50 hover:text-purple-700 transition">@lang('common.edit')</a>
                </div>

                {{-- Facebook --}}
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:border-purple-200 transition">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900">Facebook</h4>
                            <p class="text-xs text-gray-500">@lang('company_settings.facebook_desc')</p>
                        </div>
                    </div>
                    <a href="{{ route('facebook.settings') }}" class="block text-center px-4 py-2 bg-gray-100 text-gray-700 rounded-xl text-sm font-medium hover:bg-purple-50 hover:text-purple-700 transition">@lang('common.edit')</a>
                </div>

                {{-- AI --}}
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:border-purple-200 transition">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900">AI Setup</h4>
                            <p class="text-xs text-gray-500">@lang('company_settings.ai_desc')</p>
                        </div>
                    </div>
                    <a href="{{ route('ai.setup') }}" class="block text-center px-4 py-2 bg-gray-100 text-gray-700 rounded-xl text-sm font-medium hover:bg-purple-50 hover:text-purple-700 transition">@lang('common.edit')</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
