@extends('layouts.tenant')

@section('title', 'Accounting Settings - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <h1 class="text-2xl font-bold text-gray-900">@lang('accounting.settings_title')</h1>
            <p class="text-gray-600 text-sm">@lang('accounting.settings_subtitle')</p>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form method="POST" action="{{ route('accounting.settings.update') }}" class="space-y-8">
            @csrf
            @method('PUT')

            {{-- Basic --}}
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h3 class="font-bold text-gray-900 mb-4">@lang('accounting.general')</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">@lang('accounting.currency_symbol')</label>
                        <input type="text" name="currency_symbol" value="{{ $settings->currency_symbol }}" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">@lang('accounting.currency')</label>
                        <input type="text" name="currency" value="{{ $settings->currency }}" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">@lang('accounting.fiscal_year_start')</label>
                        <select name="fiscal_year_start_month" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                            @foreach([1 => __('accounting.month_jan'), 4 => __('accounting.month_apr'), 7 => __('accounting.month_jul'), 10 => __('accounting.month_oct')] as $month => $label)
                                <option value="{{ $month }}" {{ $settings->fiscal_year_start_month == $month ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Auto post --}}
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h3 class="font-bold text-gray-900 mb-1">@lang('accounting.auto_posting')</h3>
                <p class="text-sm text-gray-500 mb-4">@lang('accounting.auto_posting_subtitle')</p>
                <div class="space-y-3">
                    <label class="flex items-center justify-between p-3 border border-gray-200 rounded-xl cursor-pointer">
                        <span class="text-sm text-gray-700">@lang('accounting.auto_pos_sale')</span>
                        <input type="checkbox" name="post_pos_sales" value="1" {{ $settings->post_pos_sales ? 'checked' : '' }} class="w-5 h-5 accent-purple-600">
                    </label>
                    <label class="flex items-center justify-between p-3 border border-gray-200 rounded-xl cursor-pointer">
                        <span class="text-sm text-gray-700">@lang('accounting.auto_pos_refund')</span>
                        <input type="checkbox" name="post_pos_refunds" value="1" {{ $settings->post_pos_refunds ? 'checked' : '' }} class="w-5 h-5 accent-purple-600">
                    </label>
                    <label class="flex items-center justify-between p-3 border border-gray-200 rounded-xl cursor-pointer">
                        <span class="text-sm text-gray-700">@lang('accounting.auto_order')</span>
                        <input type="checkbox" name="post_storefront_orders" value="1" {{ $settings->post_storefront_orders ? 'checked' : '' }} class="w-5 h-5 accent-purple-600">
                    </label>
                </div>
            </div>

            {{-- Default accounts --}}
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h3 class="font-bold text-gray-900 mb-4">@lang('accounting.default_accounts')</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @php
                        $defaults = [
                            'default_cash_account_id' => __('accounting.acc_cash'),
                            'default_bank_account_id' => __('accounting.acc_bank'),
                            'default_receivable_account_id' => __('accounting.acc_receivable'),
                            'default_inventory_account_id' => __('accounting.acc_inventory'),
                            'default_cogs_account_id' => __('accounting.acc_cogs'),
                            'default_sales_account_id' => __('accounting.acc_sales'),
                            'default_discount_account_id' => __('accounting.acc_discount'),
                            'default_tax_payable_account_id' => __('accounting.acc_tax_payable'),
                            'default_expense_account_id' => __('accounting.acc_expense'),
                        ];
                    @endphp
                    @foreach($defaults as $field => $label)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ $label }}</label>
                            <select name="{{ $field }}" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                                <option value="">@lang('accounting.select')</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}" {{ $settings->{$field} == $account->id ? 'selected' : '' }}>
                                        {{ $account->code }} — {{ $account->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Payment mapping --}}
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h3 class="font-bold text-gray-900 mb-1">@lang('accounting.payment_mapping')</h3>
                <p class="text-sm text-gray-500 mb-4">@lang('accounting.payment_mapping_subtitle')</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach(['cash' => __('accounting.pay_cash'), 'bkash' => __('accounting.pay_bkash'), 'nagad' => __('accounting.pay_nagad'), 'rocket' => __('accounting.pay_rocket'), 'upay' => __('accounting.pay_upay'), 'card' => __('accounting.pay_card'), 'bank' => __('accounting.pay_bank'), 'cod' => __('accounting.pay_cod')] as $method => $label)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ $label }}</label>
                            <select name="payment_map[{{ $method }}]" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                                <option value="">@lang('accounting.select')</option>
                                @foreach($cashAccounts as $account)
                                    <option value="{{ $account->id }}" {{ ($paymentMap[$method] ?? null) == $account->id ? 'selected' : '' }}>
                                        {{ $account->code }} — {{ $account->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Opening balances --}}
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h3 class="font-bold text-gray-900 mb-1">@lang('accounting.opening_balances')</h3>
                <p class="text-sm text-gray-500 mb-4">
                    @lang('accounting.opening_subtitle')
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($accounts as $account)
                        @if(in_array($account->account_type, ['asset', 'liability', 'equity'], true))
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $account->name }}</label>
                                <input type="number" step="0.01" min="0" name="opening_balance[{{ $account->id }}]"
                                       value="{{ (float) $account->opening_balance > 0 ? $account->opening_balance : '' }}"
                                       class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm" placeholder="0">
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-purple-600 text-white rounded-xl text-sm font-medium hover:bg-purple-700">@lang('accounting.save_settings')</button>
            </div>
        </form>
    </div>
</div>
@endsection
