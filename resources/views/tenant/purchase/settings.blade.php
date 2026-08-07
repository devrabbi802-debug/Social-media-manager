@extends('layouts.tenant')

@section('title', __('sidebar.purchase_settings').' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">@lang('sidebar.purchase_settings')</h1>
                    <p class="text-gray-600">পারচেজ সিস্টেমের ডিফল্ট সেটিংস কনফিগার করুন</p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @include('tenant.purchase.partials._nav', ['current' => 'settings'])

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-2xl p-4 mb-6 flex items-center">
            <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <p class="text-green-800 font-medium">{{ session('success') }}</p>
        </div>
        @endif

        @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-2xl p-4 mb-6">
            <ul class="text-red-700 text-sm space-y-1">
                @foreach($errors->all() as $error)
                    <li>• {{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('purchase.settings.update') }}" class="max-w-3xl space-y-6">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900 mb-4">ডিফল্ট সেটিংস</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ডিফল্ট গুদাম</label>
                        <select name="default_warehouse_id" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                            <option value="">কোনোটি নেই</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ $settings->default_warehouse_id == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ইনভেন্টরি (ক্রয়) অ্যাকাউন্ট</label>
                        <select name="purchase_account_id" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                            <option value="">ডিফল্ট (Inventory 1200)</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}" {{ $settings->purchase_account_id == $account->id ? 'selected' : '' }}>{{ $account->code }} — {{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">পেমেন্ট টার্ম (দিন)</label>
                        <input type="number" name="payment_term_days" value="{{ $settings->payment_term_days }}" required min="0" max="3650"
                               class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ডিফল্ট ট্যাক্স হার (%)</label>
                        <input type="number" step="0.01" min="0" max="100" name="default_tax_rate" value="{{ $settings->default_tax_rate }}"
                               class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900 mb-4">সাপ্লায়ার পেমেন্ট মাধ্যম</h2>
                <p class="text-sm text-gray-500 mb-3">এই অ্যাকাউন্টগুলো সাপ্লায়ার পেমেন্ট/বিল পরিশোধের সময় দেখানো হবে। নতুন মাধ্যম যোগ করতে Accounting → Chart of Accounts-এ একটি Asset অ্যাকাউন্ট তৈরি করে "POS Payment Method" টিক দিন।</p>
                <div class="space-y-2">
                    @foreach($paymentAccounts as $method)
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="payment_methods[]" value="{{ $method->code }}" {{ $settings->isEnabled($method->code) ? 'checked' : '' }} class="rounded">
                            <span class="text-sm"><span class="font-medium">{{ $method->name }}</span> <span class="text-gray-400">({{ $method->code }})</span></span>
                        </label>
                    @endforeach
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">ডিফল্ট মাধ্যম</label>
                    <select name="default_payment_method" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                        @foreach($paymentAccounts as $method)
                            <option value="{{ $method->code }}" {{ $settings->defaultMethod() === $method->code ? 'selected' : '' }}>{{ $method->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900 mb-4">অটোমেশন</h2>                <div class="space-y-3">
                    <label class="flex items-center justify-between gap-4 cursor-pointer">
                        <span class="text-sm text-gray-700">রিসিভ করার সময় অটো বিল তৈরি করুন</span>
                        <input type="checkbox" name="auto_create_invoice_on_receipt" value="1" {{ $settings->auto_create_invoice_on_receipt ? 'checked' : '' }} class="w-5 h-5 text-purple-600 rounded focus:ring-purple-500">
                    </label>
                    <label class="flex items-center justify-between gap-4 cursor-pointer">
                        <span class="text-sm text-gray-700">অ্যাকাউন্টিংয়ে অটো পোস্ট করুন (বিল/পেমেন্ট/রিটার্ন)</span>
                        <input type="checkbox" name="auto_post_purchases" value="1" {{ $settings->auto_post_purchases ? 'checked' : '' }} class="w-5 h-5 text-purple-600 rounded focus:ring-purple-500">
                    </label>
                    <label class="flex items-center justify-between gap-4 cursor-pointer">
                        <span class="text-sm text-gray-700">রিসিভের সময় পণ্যের কস্ট প্রাইস আপডেট করুন</span>
                        <input type="checkbox" name="update_cost_price_on_receipt" value="1" {{ $settings->update_cost_price_on_receipt ? 'checked' : '' }} class="w-5 h-5 text-purple-600 rounded focus:ring-purple-500">
                    </label>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900 mb-4">নম্বর প্রিফিক্স</h2>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">অর্ডার (PO)</label>
                        <input type="text" name="po_prefix" value="{{ $settings->po_prefix }}" maxlength="10"
                               class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">রিসিপ্ট (GRN)</label>
                        <input type="text" name="grn_prefix" value="{{ $settings->grn_prefix }}" maxlength="10"
                               class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">বিল (INV)</label>
                        <input type="text" name="inv_prefix" value="{{ $settings->inv_prefix }}" maxlength="10"
                               class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">পেমেন্ট (PAY)</label>
                        <input type="text" name="pay_prefix" value="{{ $settings->pay_prefix }}" maxlength="10"
                               class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">রিটার্ন (RTN)</label>
                        <input type="text" name="rtn_prefix" value="{{ $settings->rtn_prefix }}" maxlength="10"
                               class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition">সেটিংস সংরক্ষণ করুন</button>
            </div>
        </form>
    </div>
</div>
@endsection
