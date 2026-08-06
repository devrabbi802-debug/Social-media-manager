@extends('layouts.tenant')

@section('title', 'POS Settings - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <h1 class="text-2xl font-bold text-gray-900">POS Settings</h1>
            <p class="text-gray-600">স্টোর, ট্যাক্স, রিসিট ও পেমেন্ট সেটিংস</p>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if(session('success'))
            <div class="mb-6 bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded-xl text-sm">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-6 bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-xl text-sm">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('pos.settings.update') }}" class="bg-white rounded-2xl shadow-sm p-6 space-y-6">
            @csrf
            @method('PUT')

            <div>
                <h3 class="font-bold text-gray-900 mb-4 text-lg">Store Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Store Name</label>
                        <input type="text" name="store_name" value="{{ old('store_name', $settings->store_name) }}" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Phone</label>
                        <input type="text" name="store_phone" value="{{ old('store_phone', $settings->store_phone) }}" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Email</label>
                        <input type="email" name="store_email" value="{{ old('store_email', $settings->store_email) }}" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Default Warehouse</label>
                        <select name="default_warehouse_id" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                            <option value="">— Select —</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ $settings->default_warehouse_id == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-600 mb-1">Store Address</label>
                    <textarea name="store_address" rows="2" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">{{ old('store_address', $settings->store_address) }}</textarea>
                </div>
            </div>

            <div class="border-t border-gray-200 pt-6">
                <h3 class="font-bold text-gray-900 mb-4 text-lg">Tax & Currency</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Tax Rate (%)</label>
                        <input type="number" name="tax_rate" min="0" max="100" step="0.01" value="{{ old('tax_rate', $settings->tax_rate) }}" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Tax Type</label>
                        <select name="tax_type" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                            <option value="inclusive" {{ $settings->tax_type === 'inclusive' ? 'selected' : '' }}>Inclusive (মূল্যে যুক্ত)</option>
                            <option value="exclusive" {{ $settings->tax_type === 'exclusive' ? 'selected' : '' }}>Exclusive (আলাদা যোগ)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Currency Code</label>
                        <input type="text" name="currency" value="{{ old('currency', $settings->currency) }}" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Currency Symbol</label>
                        <input type="text" name="currency_symbol" value="{{ old('currency_symbol', $settings->currency_symbol) }}" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-200 pt-6">
                <h3 class="font-bold text-gray-900 mb-4 text-lg">Payment Methods</h3>
                <p class="text-sm text-gray-500 mb-3">Payment methods আসে <span class="font-medium text-purple-700">Chart of Accounts</span> থেকে। নতুন পেমেন্ট মেথড যোগ করতে Accounting → Chart of Accounts-এ একটি Asset অ্যাকাউন্ট তৈরি করে "POS Payment Method" টিক দিন।</p>
                <div class="space-y-2">
                    @php $methods = $settings->methods(); @endphp
                    @forelse($paymentAccounts as $method)
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="payment_methods[]" value="{{ $method->code }}" {{ $settings->isEnabled($method->code) ? 'checked' : '' }} class="rounded">
                            <span class="text-sm"><span class="font-medium">{{ $method->name }}</span> <span class="text-gray-400">({{ $method->code }})</span></span>
                        </label>
                    @empty
                        <p class="text-sm text-gray-400">কোনো POS payment account পাওয়া যায়নি। COA-তে asset অ্যাকাউন্ট তৈরি করুন।</p>
                    @endforelse
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-600 mb-1">Default Method</label>
                    <select name="default_payment_method" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                        @foreach($paymentAccounts as $method)
                            <option value="{{ $method->code }}" {{ $settings->defaultMethod() === $method->code ? 'selected' : '' }}>{{ $method->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="border-t border-gray-200 pt-6">
                <h3 class="font-bold text-gray-900 mb-4 text-lg">Receipt</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Receipt Size</label>
                        <select name="receipt_size" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                            <option value="80mm" {{ $settings->receipt_size === '80mm' ? 'selected' : '' }}>80mm (thermal)</option>
                            <option value="58mm" {{ $settings->receipt_size === '58mm' ? 'selected' : '' }}>58mm (thermal)</option>
                            <option value="a4" {{ $settings->receipt_size === 'a4' ? 'selected' : '' }}>A4</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="auto_print_receipt" value="1" {{ $settings->auto_print_receipt ? 'checked' : '' }} class="rounded">
                            <span class="text-sm">Checkout এর পর অটো রিসিট প্রিন্ট</span>
                        </label>
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-600 mb-1">Receipt Footer</label>
                    <textarea name="receipt_footer" rows="2" placeholder="ধন্যবাদ! আবার আসবেন..." class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">{{ old('receipt_footer', $settings->receipt_footer) }}</textarea>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-purple-600 text-white rounded-xl font-semibold hover:bg-purple-700">Save Settings</button>
            </div>
        </form>
    </div>
</div>
@endsection