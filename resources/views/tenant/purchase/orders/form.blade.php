@extends('layouts.tenant')

@php
    $isEdit = (bool) ($order?->exists);
    $initialSupplier = ($order && $order->supplier) ? ['id' => $order->supplier_id, 'name' => $order->supplier->name] : null;
    $initialItems = collect($order?->items ?? [])->map(fn ($i) => [
        'product_id' => $i->product_id,
        'variant_id' => $i->variant_id,
        'purchase_order_item_id' => $i->id,
        'name' => $i->name,
        'sku' => $i->sku,
        'quantity' => (int) $i->quantity,
        'unit_cost' => (float) $i->unit_cost,
        'discount' => (float) $i->discount,
        'variants' => [],
    ])->values();
@endphp

@section('title', ($isEdit ? __('sidebar.purchase_orders').' - Edit' : __('sidebar.purchase_orders').' - Create').' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $isEdit ? 'পারচেজ অর্ডার সম্পাদনা' : 'নতুন পারচেজ অর্ডার' }}</h1>
                    @if($isEdit)
                        <p class="text-gray-600">{{ $order->po_number }} • {{ $order->statusLabel() }}</p>
                    @endif
                </div>
                <a href="{{ route('purchase.orders.index') }}" class="px-4 py-2 border rounded-xl text-gray-600 hover:bg-gray-50 transition">← Back</a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @include('tenant.purchase.partials._nav', ['current' => 'orders'])

        @if(session('error'))
        <div class="bg-red-50 border border-red-200 rounded-2xl p-4 mb-6">
            <p class="text-red-700 text-sm">• {{ session('error') }}</p>
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

        <form method="POST" action="{{ $isEdit ? route('purchase.orders.update', $order) : route('purchase.orders.store') }}"
              x-data="purchaseForm({
                  items: @js($initialItems),
                  discountType: @js($order->discount_type ?? ''),
                  discountValue: @js((float) ($order->discount_value ?? 0)),
                  taxRate: @js((float) ($order->tax_rate ?? $defaultTaxRate)),
              })"
              class="space-y-6">
            @csrf
            @if($isEdit) @method('PUT') @endif

            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900 mb-4">অর্ডার তথ্য</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">সাপ্লায়ার *</label>
                        <div x-data="supplierPicker(@js($initialSupplier))" @click.outside="open=false" class="relative z-50">
                            <input type="hidden" name="supplier_id" :value="selectedId">
                            <input type="text" x-model="query" @input="search()" @focus="open=true; search()" @keydown.escape="open=false"
                                   placeholder="সাপ্লায়ার খুঁজুন..." required
                                   class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            <div x-show="open && (results.length || loading)" x-cloak
                                 class="absolute z-50 mt-1 w-72 bg-white rounded-xl shadow-lg border max-h-64 overflow-y-auto">
                                <template x-if="loading"><div class="p-3 text-sm text-gray-500">অনুসন্ধান হচ্ছে...</div></template>
                                <template x-for="s in results" :key="s.id">
                                    <div @click="select(s)" class="px-4 py-2 hover:bg-purple-50 cursor-pointer">
                                        <p class="text-sm font-medium" x-text="s.name"></p>
                                        <p class="text-xs text-gray-500" x-text="(s.company||'') + (s.phone ? ' • '+s.phone : '')"></p>
                                    </div>
                                </template>
                                <template x-if="!loading && results.length === 0 && query.length">
                                    <div class="p-3 text-sm text-gray-500">কোনো সাপ্লায়ার পাওয়া যায়নি</div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">অর্ডার তারিখ *</label>
                        <input type="date" name="order_date" value="{{ old('order_date', $order->order_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required
                               class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">প্রত্যাশিত তারিখ</label>
                        <input type="date" name="expected_date" value="{{ old('expected_date', $order->expected_date?->format('Y-m-d')) }}"
                               class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">স্ট্যাটাস</label>
                        <select name="status" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                            <option value="draft" {{ old('status', $order->status ?? 'draft') === 'draft' ? 'selected' : '' }}>Draft (খসড়া)</option>
                            <option value="ordered" {{ old('status', $order->status ?? '') === 'ordered' ? 'selected' : '' }}>Ordered (কনফার্ম)</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-900">আইটেম সমূহ</h2>
                    <button type="button" @click="addItem()" class="px-4 py-2 bg-purple-50 text-purple-700 border border-purple-200 rounded-xl text-sm font-medium hover:bg-purple-100">+ আইটেম যোগ করুন</button>
                </div>

                <div class="overflow-x-clip">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
                                <th class="px-4 py-3 font-medium">পণ্য</th>
                                <th class="px-4 py-3 font-medium w-24">পরিমাণ</th>
                                <th class="px-4 py-3 font-medium w-32">ইউনিট কস্ট</th>
                                <th class="px-4 py-3 font-medium w-28">ছাড়</th>
                                <th class="px-4 py-3 font-medium text-right w-36">মোট</th>
                                <th class="px-4 py-3 w-10"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <template x-for="(row, index) in items" :key="index">
                                <tr x-data="productPicker(row, removeItem, true)" @click.outside="open=false" class="relative z-50 align-top">
                                    <td class="px-4 py-3">
                                        <div class="relative">
                                            <input type="text" x-model="query" @input="search()" @focus="open=true; search()" @keydown.escape="open=false"
                                                   placeholder="পণ্য খুঁজুন..." class="w-full min-w-[220px] border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                                            <div x-show="open && (results.length || loading)" x-cloak
                                                 class="absolute z-50 mt-1 w-80 bg-white rounded-xl shadow-lg border max-h-60 overflow-y-auto">
                                                <template x-if="loading"><div class="p-3 text-sm text-gray-500">অনুসন্ধান হচ্ছে...</div></template>
                                                <template x-for="p in results" :key="p.id">
                                                    <div @click="select(p)" class="px-3 py-2 hover:bg-purple-50 cursor-pointer">
                                                        <p class="text-sm font-medium" x-text="p.name"></p>
                                                        <p class="text-xs text-gray-500">
                                                            <span x-text="p.sku || '—'"></span>
                                                            <span x-show="p.has_variants"> • ভেরিয়েন্ট আছে</span>
                                                            <span x-text="' • স্টক: '+p.stock"></span>
                                                        </p>
                                                        <p class="text-xs font-semibold text-purple-600" x-text="'কস্ট: ৳'+fmt(p.cost)"></p>
                                                    </div>
                                                </template>
                                                <template x-if="!loading && results.length === 0 && query.length">
                                                    <div class="p-3 text-sm text-gray-500">কোনো পণ্য পাওয়া যায়নি</div>
                                                </template>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" min="1" x-model="row.quantity" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" step="0.01" min="0" x-model="row.unit_cost" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" step="0.01" min="0" x-model="row.discount" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold text-gray-900 whitespace-nowrap" x-text="'৳' + fmt((row.quantity||0) * ((row.unit_cost||0) - (row.discount||0)))"></td>
                                    <td class="px-4 py-3 text-right">
                                        <button type="button" @click="remove(row)" class="text-red-500 hover:text-red-700 text-lg leading-none">✕</button>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="items.length === 0">
                                <td colspan="6" class="text-center py-10 text-gray-500">আইটেম যোগ করুন</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ডিসকাউন্ট টাইপ</label>
                        <select name="discount_type" x-model="discountType" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                            <option value="">কোনো ডিসকাউন্ট নেই</option>
                            <option value="fixed">ফ্ল্যাট (৳)</option>
                            <option value="percent">শতাংশ (%)</option>
                        </select>
                    </div>
                    <div x-show="discountType" x-cloak>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ডিসকাউন্ট মূল্য</label>
                        <input type="number" step="0.01" min="0" name="discount_value" x-model="discountValue" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ট্যাক্স হার (%)</label>
                        <input type="number" step="0.01" min="0" max="100" name="tax_rate" x-model="taxRate" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>

                <input type="hidden" name="items_json" :value="itemsJson()">

                <div class="mt-6 bg-gray-50 rounded-xl p-4 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div class="text-gray-600">সাবটোটাল <span class="font-semibold text-gray-900 float-right" x-text="'৳'+fmt(subtotal)"></span></div>
                    <div class="text-gray-600">ডিসকাউন্ট <span class="font-semibold text-gray-900 float-right" x-text="'- ৳'+fmt(discountAmount)"></span></div>
                    <div class="text-gray-600">ট্যাক্স <span class="font-semibold text-gray-900 float-right" x-text="'+ ৳'+fmt(taxAmount)"></span></div>
                    <div class="text-gray-900 font-bold">মোট <span class="float-right" x-text="'৳'+fmt(total)"></span></div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">নোট</label>
                    <textarea name="notes" rows="3" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">{{ old('notes', $order->notes ?? '') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">শর্তাবলী</label>
                    <textarea name="terms" rows="3" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">{{ old('terms', $order->terms ?? '') }}</textarea>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900 mb-1">অগ্রিম পেমেন্ট (ঐচ্ছিক)</h2>
                <p class="text-sm text-gray-500 mb-4">সাপ্লায়ারকে অগ্রিম কিছু টাকা দিতে চাইলে এখানে দিতে পারেন। একাধিক মাধ্যম (নগদ + বিকাশ) একসাথে ব্যবহার করা যাবে।</p>
                <div x-data="splitPayment({ methods: @js($paymentAccounts->pluck('code')->all() ?: ['cash']), amount: 0, currencySymbol: '৳' })">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">পেমেন্ট তারিখ</label>
                        <input type="date" name="advance_date" value="{{ old('advance_date', now()->format('Y-m-d')) }}"
                               class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 md:max-w-xs">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">পেমেন্ট মাধ্যম ও পরিমাণ</label>
                        <input type="hidden" name="advance_methods_json" :value="methodsJson()">
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
                        <p class="text-sm mt-2 text-gray-500">মোট অগ্রিম: <span class="font-semibold text-gray-900" x-text="'৳' + fmt(total)"></span></p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('purchase.orders.index') }}" class="px-6 py-2 border rounded-xl text-gray-600 hover:bg-gray-50 transition">বাতিল</a>
                <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition">
                    {{ $isEdit ? 'আপডেট করুন' : 'অর্ডার তৈরি করুন' }}
                </button>
            </div>

            {{-- Variant Selection Modal (POS-style) --}}
            <div x-show="variantModalOpen" x-cloak x-transition.opacity class="fixed inset-0 z-[70] flex items-center justify-center bg-black/50 p-4" @keydown.escape.window="variantModalOpen = false">
                <div class="bg-white rounded-2xl w-full max-w-md shadow-xl overflow-hidden" @click.outside="variantModalOpen = false">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <h3 class="text-lg font-bold text-gray-900 truncate" x-text="variantProduct ? variantProduct.name : ''"></h3>
                            <p class="text-xs text-gray-500 mt-0.5">ভেরিয়েন্ট নির্বাচন করুন — একাধিক যোগ করা যাবে</p>
                        </div>
                        <button type="button" @click="variantModalOpen = false" class="shrink-0 w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-100">✕</button>
                    </div>
                    <div class="p-4 space-y-2 max-h-[50vh] overflow-y-auto">
                        <template x-for="(s, i) in variantSelections" :key="s.variant.id">
                            <div class="flex items-center gap-3 bg-gray-50 rounded-xl p-3" :class="s.qty > 0 ? 'ring-2 ring-purple-400' : ''">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-800 truncate" x-text="s.variant.name"></p>
                                    <p class="text-xs text-gray-500 mt-0.5" x-text="'কস্ট: ৳' + fmt(s.variant.cost) + ' • স্টক: ' + s.variant.stock"></p>
                                </div>
                                <div class="flex items-center gap-1">
                                    <button type="button" @click="changeVariantQty(i, -1)" class="w-8 h-8 rounded-lg bg-white border border-gray-300 text-gray-600 font-bold hover:bg-gray-100">−</button>
                                    <span class="w-8 text-center text-sm font-semibold" x-text="s.qty"></span>
                                    <button type="button" @click="changeVariantQty(i, 1)" class="w-8 h-8 rounded-lg bg-white border border-gray-300 text-gray-600 font-bold hover:bg-gray-100">+</button>
                                </div>
                                <span class="w-20 text-right text-sm font-bold text-gray-900" x-text="s.qty > 0 ? '৳' + fmt(s.variant.cost * s.qty) : ''"></span>
                            </div>
                        </template>
                    </div>
                    <div class="px-5 py-4 border-t border-gray-100 flex items-center justify-between gap-3">
                        <div class="text-sm">
                            <span class="text-gray-500">মোট:</span>
                            <span class="font-bold text-gray-900 ml-1" x-text="'৳' + fmt(variantSelectionTotal())"></span>
                            <span class="ml-2 text-xs text-gray-400" x-text="variantSelectionCount() > 0 ? (variantSelectionCount() + ' pcs') : ''"></span>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" @click="variantModalOpen = false" class="px-4 py-2 border border-gray-300 rounded-xl text-sm text-gray-600 hover:bg-gray-50">Cancel</button>
                            <button type="button" @click="addVariantSelections()" :disabled="variantSelectionCount() === 0" class="px-5 py-2 bg-purple-600 text-white rounded-xl text-sm font-semibold hover:bg-purple-700 disabled:opacity-40">আইটেমে যোগ করুন</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@include('tenant.purchase.partials._alpine')
