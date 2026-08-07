@extends('layouts.tenant')

@section('title', __('sidebar.supplier_payments').' - Create - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">নতুন সাপ্লায়ার পেমেন্ট</h1>
                    <p class="text-gray-600">সাপ্লায়ারকে পেমেন্ট দিন — চাইলে নির্দিষ্ট বিলের সাথে লিংক করুন</p>
                </div>
                <a href="{{ route('purchase.payments.index') }}" class="px-4 py-2 border rounded-xl text-gray-600 hover:bg-gray-50 transition">← Back</a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @include('tenant.purchase.partials._nav', ['current' => 'payments'])

        @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-2xl p-4 mb-6">
            <ul class="text-red-700 text-sm space-y-1">
                @foreach($errors->all() as $error)
                    <li>• {{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('purchase.payments.store') }}"
              x-data="paymentForm({
                  supplier: @js($defaultSupplier ? ['id' => $defaultSupplier->id, 'name' => $defaultSupplier->name] : null),
                  invoice: @js($defaultInvoice ? ['id' => $defaultInvoice->id] : null),
              })"
              class="max-w-3xl space-y-6">            @csrf

            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900 mb-4">পেমেন্ট তথ্য</h2>
                <div class="space-y-4">
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 mb-1">সাপ্লায়ার *</label>
                        <input type="hidden" name="supplier_id" :value="supplierId">
                        <input type="text" x-model="supplierQuery" @input="searchSuppliers()" @focus="supplierOpen=true" @keydown.escape="supplierOpen=false"
                               placeholder="সাপ্লায়ার খুঁজুন..." required
                               class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        <div x-show="supplierOpen && (supplierResults.length || supplierLoading)" x-cloak
                             class="absolute z-20 mt-1 w-full bg-white rounded-xl shadow-lg border max-h-64 overflow-y-auto">
                            <template x-if="supplierLoading"><div class="p-3 text-sm text-gray-500">অনুসন্ধান হচ্ছে...</div></template>
                            <template x-for="s in supplierResults" :key="s.id">
                                <div @click="selectSupplier(s)" class="px-4 py-2 hover:bg-purple-50 cursor-pointer">
                                    <p class="text-sm font-medium" x-text="s.name"></p>
                                    <p class="text-xs text-gray-500" x-text="(s.company||'') + (s.phone ? ' • '+s.phone : '')"></p>
                                </div>
                            </template>
                            <template x-if="!supplierLoading && supplierResults.length === 0 && supplierQuery.length">
                                <div class="p-3 text-sm text-gray-500">কোনো সাপ্লায়ার পাওয়া যায়নি</div>
                            </template>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">লিংক করা বিল (ঐচ্ছিক)</label>
                        <select name="purchase_invoice_id" x-model="invoiceId" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                            <option value="">কোনো বিলের সাথে লিংক করবেন না</option>
                            <template x-if="invoiceLoading"><option>বিল লোড হচ্ছে...</option></template>
                            <template x-for="inv in invoices" :key="inv.id">
                                <option :value="inv.id" x-text="inv.invoice_number + ' — বাকি ৳' + (Number(inv.due)||0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})"></option>
                            </template>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">সাপ্লায়ার নির্বাচন করলে তার খোলা (বাকি থাকা) বিলগুলো এখানে আসবে।</p>
                    </div>

                    <div x-data="splitPayment({ methods: @js($paymentAccounts->pluck('code')->all() ?: ['cash']), amount: {{ (float) old('amount', 0) }}, currencySymbol: '৳' })">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">পরিমাণ (৳) *</label>
                                <input type="number" step="0.01" min="0.01" name="amount" x-model="amount" required
                                       class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">পেমেন্ট তারিখ *</label>
                                <input type="date" name="payment_date" value="{{ old('payment_date', now()->format('Y-m-d')) }}" required
                                       class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">রেফারেন্স</label>
                                <input type="text" name="reference" value="{{ old('reference') }}"
                                       class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                            </div>
                        </div>

                        <div class="border-t border-gray-100 pt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">পেমেন্ট মাধ্যম * <span class="text-xs text-gray-400">(একাধিক মাধ্যম ব্যবহার করতে পারবেন — যেমন নগদ + বিকাশ)</span></label>
                            <input type="hidden" name="methods_json" :value="methodsJson()">
                            <template x-for="(row, index) in rows" :key="index">
                                <div class="flex items-center gap-2 mb-2">
                                    <select x-model="row.method" class="flex-1 border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                                        @foreach($paymentAccounts as $account)
                                            <option value="{{ $account->code }}">{{ $account->name }}</option>
                                        @endforeach
                                        @if($paymentAccounts->isEmpty())
                                            <option value="cash">Cash</option>
                                        @endif
                                    </select>
                                    <input type="number" step="0.01" min="0" x-model="row.amount" placeholder="টাকা"
                                           class="w-32 border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                                    <input type="text" x-model="row.reference" placeholder="রেফারেন্স"
                                           class="flex-1 border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                                    <button type="button" @click="removeRow(row)" class="text-red-500 hover:text-red-700 text-lg leading-none px-1" x-show="rows.length > 1">✕</button>
                                </div>
                            </template>
                            <button type="button" @click="addRow()" class="mt-1 text-sm text-purple-600 hover:text-purple-800 font-medium">+ আরেকটি মাধ্যম যোগ করুন</button>
                            <p class="text-sm mt-2">
                                <span class="text-gray-600">মোট:</span>
                                <span class="font-semibold text-gray-900" x-text="'৳' + fmt(total)"></span>
                                <span class="text-gray-400 text-xs ml-2" x-show="total !== (Number(amount)||0)">— পরিমাণের সাথে মিলবে না!</span>
                            </p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">নোট</label>
                        <textarea name="notes" rows="2" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('purchase.payments.index') }}" class="px-6 py-2 border rounded-xl text-gray-600 hover:bg-gray-50 transition">বাতিল</a>
                <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-xl font-medium hover:bg-green-700 transition">পেমেন্ট করুন</button>
            </div>
        </form>
    </div>
</div>
@endsection

@include('tenant.purchase.partials._alpine')
