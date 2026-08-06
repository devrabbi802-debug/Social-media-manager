@extends('layouts.tenant')

@section('title', ($type === 'income' ? 'Record Income' : 'Record Expense').' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-2xl mx-auto px-4 py-10">
        <div class="bg-white rounded-2xl shadow-sm p-6 sm:p-8">
            <h1 class="text-xl font-bold text-gray-900 mb-1">
                {{ $type === 'income' ? __('accounting.money_create_income') : __('accounting.money_create_expense') }}
            </h1>
            <p class="text-sm text-gray-500 mb-6">
                @if($type === 'income')
                    @lang('accounting.income_subtitle')
                @else
                    @lang('accounting.expense_subtitle')
                @endif
            </p>

            <form method="POST" action="{{ route('accounting.money.store') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="type" value="{{ $type }}">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">@lang('accounting.amount_label') <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm" placeholder="e.g. 5000" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        {{ $type === 'income' ? __('accounting.income_category') : __('accounting.expense_category') }} <span class="text-red-500">*</span>
                    </label>
                    <select name="account_id" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm" required>
                        <option value="">@lang('accounting.select_category')</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}" {{ old('account_id') == $account->id ? 'selected' : '' }}>
                                {{ $account->name }}
                            </option>
                        @endforeach
                    </select>
                    @if($accounts->isEmpty())
                        <p class="text-xs text-red-500 mt-1">@lang('accounting.no_category')</p>
                    @endif
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">@lang('accounting.payment_method_label') <span class="text-red-500">*</span></label>
                    <select name="payment_method" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm" required>
                        @foreach(['cash' => __('accounting.pay_cash'), 'bank' => __('accounting.pay_bank'), 'bkash' => __('accounting.pay_bkash'), 'nagad' => __('accounting.pay_nagad'), 'rocket' => __('accounting.pay_rocket'), 'upay' => __('accounting.pay_upay'), 'card' => __('accounting.pay_card')] as $value => $label)
                            <option value="{{ $value }}" {{ old('payment_method') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">@lang('accounting.description_optional')</label>
                    <textarea name="narration" rows="2" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">{{ old('narration') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">@lang('accounting.date')</label>
                    <input type="date" name="entry_date" value="{{ old('entry_date', now()->toDateString()) }}" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm" required>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <a href="{{ route('accounting.money.index') }}" class="text-sm text-gray-500 hover:text-gray-700">@lang('accounting.cancel')</a>
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-sm font-medium text-white {{ $type === 'income' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700' }}">
                        {{ $type === 'income' ? __('accounting.save_income') : __('accounting.save_expense') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
