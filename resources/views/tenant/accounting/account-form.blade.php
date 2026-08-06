@extends('layouts.tenant')

@section('title', ($account ? __('accounting.edit_account') : __('accounting.create_account')).' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-2xl mx-auto px-4 py-10">
        <div class="bg-white rounded-2xl shadow-sm p-6 sm:p-8">
            <h1 class="text-xl font-bold text-gray-900 mb-1">{{ $account ? __('accounting.edit_account') : __('accounting.create_account') }}</h1>
            <p class="text-sm text-gray-500 mb-6">@lang('accounting.account_form_subtitle')</p>

            <form method="POST" action="{{ $account ? route('accounting.chart-of-accounts.update', $account) : route('accounting.chart-of-accounts.store') }}" class="space-y-5">
                @csrf
                @if($account)
                    @method('PUT')
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">@lang('accounting.account_type') <span class="text-red-500">*</span></label>
                    <select name="account_type" id="account_type" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm" {{ $account?->is_system ? 'disabled' : '' }} required>
                        @foreach($types as $value => $label)
                            <option value="{{ $value }}" {{ old('account_type', $account?->account_type) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @if($account?->is_system)
                        <input type="hidden" name="account_type" value="{{ $account->account_type }}">
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">@lang('accounting.code') <span class="text-red-500">*</span></label>
                        <input type="text" name="code" value="{{ old('code', $account?->code) }}" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm" placeholder="e.g. 1040" {{ $account?->is_system ? 'disabled' : '' }} required>
                        @if($account?->is_system)
                            <input type="hidden" name="code" value="{{ $account->code }}">
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">@lang('accounting.name') <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $account?->name) }}" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm" required>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">@lang('accounting.normal_balance')</label>
                        <select name="normal_balance" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm" {{ $account?->is_system ? 'disabled' : '' }}>
                            <option value="debit" {{ old('normal_balance', $account?->normal_balance) === 'debit' ? 'selected' : '' }}>@lang('accounting.balance_debit')</option>
                            <option value="credit" {{ old('normal_balance', $account?->normal_balance) === 'credit' ? 'selected' : '' }}>@lang('accounting.balance_credit')</option>
                        </select>
                        @if($account?->is_system)
                            <input type="hidden" name="normal_balance" value="{{ $account->normal_balance }}">
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">@lang('accounting.parent_account') <span class="text-gray-400">(@lang('accounting.optional'))</span></label>
                        <select name="parent_id" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                            <option value="">@lang('accounting.none')</option>
                            @foreach($parents as $parent)
                                <option value="{{ $parent->id }}" {{ old('parent_id', $account?->parent_id) == $parent->id ? 'selected' : '' }}>
                                    {{ $parent->code }} — {{ $parent->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">@lang('accounting.description_optional')</label>
                    <textarea name="description" rows="2" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">{{ old('description', $account?->description) }}</textarea>
                </div>

                <label class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="is_pos_payment" value="1" {{ old('is_pos_payment', $account?->is_pos_payment) ? 'checked' : '' }} class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                    <span class="text-sm text-gray-700">{{ $account?->is_system ? __('accounting.pos_system_note') : __('accounting.is_pos_payment') }}</span>
                </label>

                <div class="flex items-center justify-between pt-2">
                    <a href="{{ route('accounting.chart-of-accounts.index') }}" class="text-sm text-gray-500 hover:text-gray-700">@lang('accounting.cancel')</a>
                    <button type="submit" class="px-5 py-2.5 bg-purple-600 text-white rounded-xl text-sm font-medium hover:bg-purple-700">
                        {{ $account ? __('accounting.update') : __('accounting.save_account') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('account_type').addEventListener('change', function () {
        const value = this.value;
        const select = document.querySelector('[name="normal_balance"]');
        if (value === 'asset' || value === 'expense') {
            select.value = 'debit';
        } else {
            select.value = 'credit';
        }
    });
</script>
@endpush
@endsection
