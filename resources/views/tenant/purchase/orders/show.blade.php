@extends('layouts.tenant')

@section('title', $order->po_number.' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $order->po_number }}</h1>
                    <p class="text-gray-600">@include('tenant.purchase.partials._order-status', ['status' => $order->status])</p>
                </div>
                <div class="flex gap-2 flex-wrap">
                    @if($order->isReceivable())
                        <a href="{{ route('purchase.receipts.create', ['po_id' => $order->id]) }}" class="px-4 py-2 bg-green-600 text-white rounded-xl font-medium hover:bg-green-700 transition">+ রিসিভ করুন</a>
                    @endif
                    @if($order->status === 'draft')
                        <form method="POST" action="{{ route('purchase.orders.mark-ordered', $order) }}">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-xl font-medium hover:bg-blue-700 transition">কনফার্ম অর্ডার</button>
                        </form>
                    @endif
                    @if(in_array($order->status, ['draft', 'ordered', 'partially_received']))
                        <a href="{{ route('purchase.orders.edit', $order) }}" class="px-4 py-2 bg-white text-purple-600 border border-purple-300 rounded-xl font-medium hover:bg-purple-50 transition">Edit</a>
                        <form method="POST" action="{{ route('purchase.orders.cancel', $order) }}" onsubmit="return confirm('অর্ডার বাতাল করবেন?');">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-white text-red-600 border border-red-200 rounded-xl font-medium hover:bg-red-50 transition">বাতিল</button>
                        </form>
                    @endif
                    <a href="{{ route('purchase.orders.print', $order) }}" target="_blank" class="px-4 py-2 bg-white border rounded-xl text-gray-600 hover:bg-gray-50 transition">Print</a>
                    @if($order->status === 'draft' && ! $order->receipts()->exists())
                        <form method="POST" action="{{ route('purchase.orders.destroy', $order) }}" onsubmit="return confirm('অর্ডার ডিলিট করবেন?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="px-4 py-2 bg-white text-red-600 border border-red-200 rounded-xl font-medium hover:bg-red-50 transition">Delete</button>
                        </form>
                    @endif
                    <a href="{{ route('purchase.orders.index') }}" class="px-4 py-2 border rounded-xl text-gray-600 hover:bg-gray-50 transition">← Back</a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        @include('tenant.purchase.partials._nav', ['current' => 'orders'])

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-2xl p-4 flex items-center">
            <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <p class="text-green-800 font-medium">{{ session('success') }}</p>
        </div>
        @endif

        @if(session('error'))
        <div class="bg-red-50 border border-red-200 rounded-2xl p-4 flex items-center">
            <svg class="w-6 h-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            <p class="text-red-800 font-medium">{{ session('error') }}</p>
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h2 class="text-sm font-semibold text-gray-500 uppercase mb-3">সাপ্লায়ার তথ্য</h2>
                <a href="{{ route('purchase.suppliers.show', $order->supplier) }}" class="text-lg font-bold text-purple-600 hover:underline">{{ $order->supplier->name }}</a>
                @if($order->supplier->company)<p class="text-gray-600 text-sm">{{ $order->supplier->company }}</p>@endif
                @if($order->supplier->phone)<p class="text-gray-600 text-sm">📞 {{ $order->supplier->phone }}</p>@endif
                @if($order->supplier->email)<p class="text-gray-600 text-sm">✉️ {{ $order->supplier->email }}</p>@endif
                @if($order->supplier->address)<p class="text-gray-600 text-sm mt-2">{{ $order->supplier->address }}</p>@endif
                <div class="mt-4 pt-4 border-t text-sm space-y-1">
                    <p class="text-gray-600">অর্ডার তারিখ: <span class="font-medium text-gray-900">{{ $order->order_date->format('d M Y') }}</span></p>
                    @if($order->expected_date)<p class="text-gray-600">প্রত্যাশিত: <span class="font-medium text-gray-900">{{ $order->expected_date->format('d M Y') }}</span></p>@endif
                    @if($order->creator)<p class="text-gray-600">তৈরি করেছেন: <span class="font-medium text-gray-900">{{ $order->creator->name }}</span></p>@endif
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h2 class="text-sm font-semibold text-gray-500 uppercase mb-3">রিসিভ প্রগ্রেস</h2>
                @php
                    $totalQty = $order->items->sum('quantity');
                    $receivedQty = $order->totalReceivedQty();
                    $pct = $totalQty > 0 ? round($receivedQty / $totalQty * 100) : 0;
                @endphp
                <div class="flex items-end justify-between mb-2">
                    <span class="text-3xl font-bold text-gray-900">{{ $receivedQty }}<span class="text-lg text-gray-500">/{{ $totalQty }}</span></span>
                    <span class="text-sm font-medium text-purple-600">{{ $pct }}%</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-3">
                    <div class="bg-purple-600 h-3 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                </div>
                @if(in_array($order->status, ['ordered', 'partially_received']))
                    <a href="{{ route('purchase.receipts.create', ['po_id' => $order->id]) }}" class="mt-4 inline-block text-sm font-medium text-green-600 hover:text-green-800">+ নতুন রিসিভ যোগ করুন</a>
                @endif
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h2 class="text-sm font-semibold text-gray-500 uppercase mb-3">সারাংশ</h2>
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between text-gray-600"><span>সাবটোটাল</span><span class="font-medium text-gray-900">৳{{ number_format($order->subtotal, 2) }}</span></div>
                    @if($order->discount_amount > 0)
                        <div class="flex justify-between text-gray-600">
                            <span>ডিসকাউন্ট @if($order->discount_type === 'percent')({{ $order->discount_value }}%)@endif</span>
                            <span class="font-medium text-gray-900">-৳{{ number_format($order->discount_amount, 2) }}</span>
                        </div>
                    @endif
                    @if($order->tax_amount > 0)
                        <div class="flex justify-between text-gray-600"><span>ট্যাক্স ({{ $order->tax_rate }}%)</span><span class="font-medium text-gray-900">+৳{{ number_format($order->tax_amount, 2) }}</span></div>
                    @endif
                    <div class="flex justify-between text-base font-bold text-gray-900 pt-2 border-t"><span>মোট</span><span>৳{{ number_format($order->total, 2) }}</span></div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h2 class="text-sm font-semibold text-gray-500 uppercase mb-3">অগ্রিম পেমেন্ট</h2>
                <div class="space-y-1 text-sm mb-4">
                    <div class="flex justify-between text-gray-600"><span>মোট অগ্রিম দেওয়া</span><span class="font-medium text-green-600">৳{{ number_format($order->advanceTotal(), 2) }}</span></div>
                    <div class="flex justify-between text-gray-600"><span>বিলে প্রয়োগ হয়েছে</span><span class="font-medium text-gray-900">৳{{ number_format($order->advanceTotal() - $order->remainingAdvance(), 2) }}</span></div>
                    <div class="flex justify-between text-base font-bold text-gray-900 pt-2 border-t"><span>বাকি অগ্রিম</span><span>৳{{ number_format($order->remainingAdvance(), 2) }}</span></div>
                </div>

                @if(in_array($order->status, ['ordered', 'partially_received']))
                <form method="POST" action="{{ route('purchase.orders.pay-advance', $order) }}"
                      x-data="splitPayment({ methods: @js($paymentAccounts->pluck('code')->all() ?: ['cash']), amount: {{ $order->maxAdvanceable() }}, currencySymbol: '৳' })">
                    @csrf
                    @if($order->maxAdvanceable() <= 0)
                        <p class="text-sm text-amber-600 font-medium mb-2">এই অর্ডারের জন্য আর অগ্রিম দেওয়া যাবে না।</p>
                    @endif
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">পরিমাণ (৳)</label>
                            <input type="number" step="0.01" min="0.01" max="{{ $order->maxAdvanceable() }}" name="amount" x-model="amount" required
                                   class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                            <p class="text-xs text-gray-400 mt-1">সর্বোচ্চ: ৳{{ number_format($order->maxAdvanceable(), 2) }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">তারিখ</label>
                            <input type="date" name="payment_date" value="{{ now()->format('Y-m-d') }}" required
                                   class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">পেমেন্ট মাধ্যম <span class="text-xs text-gray-400">(নগদ + বিকাশ একসাথে)</span></label>
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
                                       class="w-28 border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                                <button type="button" @click="removeRow(row)" class="text-red-500 hover:text-red-700 text-lg leading-none px-1" x-show="rows.length > 1">✕</button>
                            </div>
                        </template>
                        <button type="button" @click="addRow()" class="mt-1 text-sm text-purple-600 hover:text-purple-800 font-medium">+ আরেকটি মাধ্যম</button>
                        <p class="text-sm mt-2 text-gray-500">মোট: <span class="font-semibold text-gray-900" x-text="'৳' + fmt(total)"></span></p>
                    </div>
                    <button type="submit" class="mt-4 w-full px-4 py-2 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition">অগ্রিম পেমেন্ট করুন</button>
                </form>
                @endif

                @if($order->advancePayments->isNotEmpty())
                <div class="mt-5 border-t border-gray-100 pt-4">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase mb-2">অগ্রিম ইতিহাস</h3>
                    <div class="space-y-3 text-sm">
                        @foreach($order->advancePayments as $adv)
                        <div class="bg-gray-50 rounded-xl p-3">
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="font-medium text-gray-900">{{ $adv->payment_number }}</span>
                                    <span class="text-gray-400 text-xs block">{{ $adv->payment_date->format('d M Y') }}</span>
                                </div>
                                <span class="font-semibold text-green-600">৳{{ number_format($adv->amount, 2) }}</span>
                            </div>
                            <div class="mt-2 space-y-1 border-t border-gray-200 pt-2">
                                @forelse($adv->methods as $m)
                                    <div class="flex justify-between text-xs text-gray-600">
                                        <span>{{ $m->methodName() }}{{ $m->reference ? ' ('.$m->reference.')' : '' }}</span>
                                        <span class="font-medium text-gray-900">৳{{ number_format($m->amount, 2) }}</span>
                                    </div>
                                @empty
                                    <div class="text-xs text-gray-500">{{ $adv->methodName() }}: ৳{{ number_format($adv->amount, 2) }}</div>
                                @endforelse
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-bold text-gray-900">আইটেম সমূহ</h2>
            </div>
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
                        <th class="px-6 py-3 font-medium">পণ্য</th>
                        <th class="px-6 py-3 font-medium text-center">পরিমাণ</th>
                        <th class="px-6 py-3 font-medium text-center">রিসিভড</th>
                        <th class="px-6 py-3 font-medium text-center">বাকি</th>
                        <th class="px-6 py-3 font-medium text-right">ইউনিট কস্ট</th>
                        <th class="px-6 py-3 font-medium text-right">ছাড়</th>
                        <th class="px-6 py-3 font-medium text-right">মোট</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($order->items as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3">
                            <p class="font-medium text-gray-900">{{ $item->name }}</p>
                            @if($item->sku)<p class="text-xs text-gray-500">SKU: {{ $item->sku }}</p>@endif
                        </td>
                        <td class="px-6 py-3 text-center">{{ $item->quantity }}</td>
                        <td class="px-6 py-3 text-center text-green-600 font-medium">{{ $item->received_quantity }}</td>
                        <td class="px-6 py-3 text-center {{ $item->remainingQuantity() > 0 ? 'text-orange-600 font-medium' : 'text-gray-400' }}">{{ $item->remainingQuantity() }}</td>
                        <td class="px-6 py-3 text-right">৳{{ number_format($item->unit_cost, 2) }}</td>
                        <td class="px-6 py-3 text-right text-red-600">-৳{{ number_format($item->discount, 2) }}</td>
                        <td class="px-6 py-3 text-right font-semibold">৳{{ number_format($item->line_total, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-6 py-10 text-center text-gray-500">কোনো আইটেম নেই</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-bold text-gray-900">রিসিপ্ট / GRN</h2>
            </div>
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
                        <th class="px-6 py-3 font-medium">রিসিপ্ট নম্বর</th>
                        <th class="px-6 py-3 font-medium">তারিখ</th>
                        <th class="px-6 py-3 font-medium">গুদাম</th>
                        <th class="px-6 py-3 font-medium text-right">মোট</th>
                        <th class="px-6 py-3 font-medium text-right">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($order->receipts as $receipt)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3"><a href="{{ route('purchase.receipts.show', $receipt) }}" class="font-medium text-purple-600 hover:underline">{{ $receipt->receipt_number }}</a></td>
                        <td class="px-6 py-3 text-sm text-gray-500">{{ $receipt->receipt_date->format('d M Y') }}</td>
                        <td class="px-6 py-3 text-sm">{{ $receipt->warehouse?->name ?? '—' }}</td>
                        <td class="px-6 py-3 text-right font-medium">৳{{ number_format($receipt->total, 2) }}</td>
                        <td class="px-6 py-3 text-right">
                            <a href="{{ route('purchase.receipts.show', $receipt) }}" class="text-purple-600 hover:text-purple-800 text-xs font-medium">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-6 py-10 text-center text-gray-500">কোনো রিসিপ্ট নেই</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($order->notes || $order->terms)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @if($order->notes)
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h2 class="text-sm font-semibold text-gray-500 uppercase mb-2">নোট</h2>
                <p class="text-gray-700 text-sm whitespace-pre-line">{{ $order->notes }}</p>
            </div>
            @endif
            @if($order->terms)
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h2 class="text-sm font-semibold text-gray-500 uppercase mb-2">শর্তাবলী</h2>
                <p class="text-gray-700 text-sm whitespace-pre-line">{{ $order->terms }}</p>
            </div>
            @endif
        </div>
        @endif
    </div>
</div>
@endsection

@include('tenant.purchase.partials._alpine')
