@extends('layouts.tenant')

@php
    $hasReceipt = ! is_null($receipt);
    $initialSupplier = $hasReceipt ? ['id' => $receipt->supplier_id, 'name' => $receipt->supplier->name] : null;
    $initialItems = $hasReceipt
        ? collect($receipt->items)->map(fn ($i) => [
            'product_id' => $i->product_id,
            'variant_id' => $i->variant_id,
            'purchase_order_item_id' => null,
            'name' => $i->name,
            'sku' => $i->sku,
            'quantity' => (int) $i->quantity,
            'unit_cost' => (float) $i->unit_cost,
            'discount' => 0,
            'variants' => [],
        ])->values()
        : collect();
@endphp

@section('title', __('sidebar.purchase_returns').' - Create - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">নতুন পারচেজ রিটার্ন</h1>
                    @if($hasReceipt)
                        <p class="text-gray-600">রিসিপ্ট: {{ $receipt->receipt_number }} • সাপ্লায়ার: {{ $receipt->supplier->name }}</p>
                    @endif
                </div>
                <a href="{{ route('purchase.returns.index') }}" class="px-4 py-2 border rounded-xl text-gray-600 hover:bg-gray-50 transition">← Back</a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @include('tenant.purchase.partials._nav', ['current' => 'returns'])

        @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-2xl p-4 mb-6">
            <ul class="text-red-700 text-sm space-y-1">
                @foreach($errors->all() as $error)
                    <li>• {{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('purchase.returns.store') }}"
              x-data="purchaseForm({ items: @js($initialItems), discountType: '', discountValue: 0, taxRate: 0 })"
              class="space-y-6">
            @csrf

            <input type="hidden" name="purchase_receipt_id" value="{{ $receipt?->id ?? '' }}">

            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900 mb-4">রিটার্ন তথ্য</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="relative">
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">গুদাম *</label>
                        <select name="warehouse_id" required class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                            <option value="">গুদাম বাছাই করুন...</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ old('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">রিটার্ন তারিখ *</label>
                        <input type="date" name="return_date" value="{{ old('return_date', now()->format('Y-m-d')) }}" required
                               class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-900">ফেরত দেওয়া আইটেম</h2>
                    <button type="button" @click="addItem()" class="px-4 py-2 bg-purple-50 text-purple-700 border border-purple-200 rounded-xl text-sm font-medium hover:bg-purple-100">+ আইটেম যোগ করুন</button>
                </div>

                <div class="overflow-x-clip">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
                                <th class="px-4 py-3 font-medium">পণ্য</th>
                                <th class="px-4 py-3 font-medium w-24">পরিমাণ</th>
                                <th class="px-4 py-3 font-medium w-32">ইউনিট কস্ট</th>
                                <th class="px-4 py-3 font-medium text-right w-36">মোট</th>
                                <th class="px-4 py-3 w-10"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <template x-for="(row, index) in items" :key="index">
                                <tr x-data="productPicker(row, removeItem)" @click.outside="open=false" class="relative z-50 align-top">
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
                                            <select x-show="row.variants && row.variants.length" x-model="row.variant_id" @change="selectVariant()"
                                                    class="mt-1 w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                                                <option value="">ভেরিয়েন্ট বাছাই করুন...</option>
                                                <template x-for="v in row.variants" :key="v.id">
                                                    <option :value="v.id" x-text="v.name + ' (স্টক: '+v.stock+')'"></option>
                                                </template>
                                            </select>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" min="1" x-model="row.quantity" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" step="0.01" min="0" x-model="row.unit_cost" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold text-gray-900 whitespace-nowrap" x-text="'৳' + fmt((row.quantity||0) * (row.unit_cost||0))"></td>
                                    <td class="px-4 py-3 text-right">
                                        <button type="button" @click="remove(row)" class="text-red-500 hover:text-red-700 text-lg leading-none">✕</button>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="items.length === 0">
                                <td colspan="5" class="text-center py-10 text-gray-500">আইটেম যোগ করুন</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <input type="hidden" name="items_json" :value="itemsJson()">

                <div class="mt-6 bg-gray-50 rounded-xl p-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div class="text-gray-600">মোট ফেরত মূল্য</div>
                    <div class="text-gray-900 font-bold text-right" x-text="'৳'+fmt(subtotal)"></div>
                </div>

                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">রিটার্নের কারণ</label>
                    <textarea name="reason" rows="3" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">{{ old('reason') }}</textarea>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('purchase.returns.index') }}" class="px-6 py-2 border rounded-xl text-gray-600 hover:bg-gray-50 transition">বাতিল</a>
                <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition">রিটার্ন সম্পন্ন করুন</button>
            </div>
        </form>
    </div>
</div>
@endsection

@include('tenant.purchase.partials._alpine')
