@extends('layouts.tenant')

@section('title', $invoice->invoice_number.' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $invoice->invoice_number }}</h1>
                    <p class="text-gray-600">@include('tenant.purchase.partials._invoice-status', ['status' => $invoice->status])</p>
                </div>
                <div class="flex gap-2 flex-wrap">
                    @if(in_array($invoice->status, ['awaiting_payment', 'partially_paid']))
                        <a href="#pay-section" class="px-4 py-2 bg-green-600 text-white rounded-xl font-medium hover:bg-green-700 transition">+ পেমেন্ট দিন</a>
                    @endif
                    @if($invoice->status === 'draft')
                        <a href="{{ route('purchase.invoices.edit', $invoice) }}" class="px-4 py-2 bg-white text-purple-600 border border-purple-300 rounded-xl font-medium hover:bg-purple-50 transition">Edit</a>
                        <form method="POST" action="{{ route('purchase.invoices.destroy', $invoice) }}" onsubmit="return confirm('বিল ডিলিট করবেন?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="px-4 py-2 bg-white text-red-600 border border-red-200 rounded-xl font-medium hover:bg-red-50 transition">Delete</button>
                        </form>
                    @endif
                    @if(in_array($invoice->status, ['awaiting_payment', 'partially_paid']))
                        <form method="POST" action="{{ route('purchase.invoices.cancel', $invoice) }}" onsubmit="return confirm('বিল বাতিল করবেন?');">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-white text-red-600 border border-red-200 rounded-xl font-medium hover:bg-red-50 transition">বাতিল</button>
                        </form>
                    @endif
                    <a href="{{ route('purchase.invoices.print', $invoice) }}" target="_blank" class="px-4 py-2 bg-white border rounded-xl text-gray-600 hover:bg-gray-50 transition">Print</a>
                    <a href="{{ route('purchase.invoices.index') }}" class="px-4 py-2 border rounded-xl text-gray-600 hover:bg-gray-50 transition">← Back</a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        @include('tenant.purchase.partials._nav', ['current' => 'invoices'])

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
                <a href="{{ route('purchase.suppliers.show', $invoice->supplier) }}" class="text-lg font-bold text-purple-600 hover:underline">{{ $invoice->supplier->name }}</a>
                @if($invoice->supplier->company)<p class="text-gray-600 text-sm">{{ $invoice->supplier->company }}</p>@endif
                @if($invoice->supplier->phone)<p class="text-gray-600 text-sm">📞 {{ $invoice->supplier->phone }}</p>@endif
                @if($invoice->supplier->address)<p class="text-gray-600 text-sm mt-2">{{ $invoice->supplier->address }}</p>@endif
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h2 class="text-sm font-semibold text-gray-500 uppercase mb-3">বিল তথ্য</h2>
                <div class="space-y-1 text-sm">
                    <p class="text-gray-600">বিল তারিখ: <span class="font-medium text-gray-900">{{ $invoice->invoice_date->format('d M Y') }}</span></p>
                    <p class="text-gray-600">ডিউ তারিখ: <span class="font-medium {{ $invoice->isOverdue() ? 'text-red-600' : 'text-gray-900' }}">{{ $invoice->due_date?->format('d M Y') ?? '—' }}</span></p>
                    @if($invoice->purchaseOrder)
                        <p class="text-gray-600">অর্ডার: <a href="{{ route('purchase.orders.show', $invoice->purchaseOrder) }}" class="font-medium text-purple-600 hover:underline">{{ $invoice->purchaseOrder->po_number }}</a></p>
                    @endif
                    @if($invoice->purchaseReceipt)
                        <p class="text-gray-600">রিসিপ্ট: <a href="{{ route('purchase.receipts.show', $invoice->purchaseReceipt) }}" class="font-medium text-purple-600 hover:underline">{{ $invoice->purchaseReceipt->receipt_number }}</a></p>
                    @endif
                    @if($invoice->creator)<p class="text-gray-600">তৈরি করেছেন: <span class="font-medium text-gray-900">{{ $invoice->creator->name }}</span></p>@endif
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6 border-l-4 border-orange-400">
                <h2 class="text-sm font-semibold text-gray-500 uppercase mb-3">পেমেন্ট সারাংশ</h2>
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between text-gray-600"><span>মোট বিল</span><span class="font-medium text-gray-900">৳{{ number_format($invoice->total, 2) }}</span></div>
                    @if($invoice->advance_applied > 0)
                        <div class="flex justify-between text-gray-600"><span>অগ্রিম প্রয়োগ</span><span class="font-medium text-purple-600">-৳{{ number_format($invoice->advance_applied, 2) }}</span></div>
                    @endif
                    <div class="flex justify-between text-gray-600"><span>পরিশোধিত</span><span class="font-medium text-green-600">৳{{ number_format($invoice->paid_amount, 2) }}</span></div>
                    <div class="flex justify-between text-base font-bold text-gray-900 pt-2 border-t"><span>বাকি</span><span class="{{ $invoice->due() > 0 ? 'text-orange-600' : 'text-green-600' }}">৳{{ number_format($invoice->due(), 2) }}</span></div>
                </div>
            </div>
        </div>

        @if(in_array($invoice->status, ['awaiting_payment', 'partially_paid']))
        <div id="pay-section" class="bg-white rounded-2xl shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">পেমেন্ট দিন</h2>
            <form method="POST" action="{{ route('purchase.invoices.pay', $invoice) }}"
                  x-data="splitPayment({ methods: @js($paymentAccounts->pluck('code')->all() ?: ['cash']), amount: {{ $invoice->due() }}, currencySymbol: '৳' })"
                  class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">পরিমাণ (৳) *</label>
                        <input type="number" step="0.01" min="0.01" max="{{ $invoice->due() }}" name="amount" x-model="amount" required
                               class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">তারিখ *</label>
                        <input type="date" name="payment_date" value="{{ now()->format('Y-m-d') }}" required
                               class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">পেমেন্ট মাধ্যম * <span class="text-xs text-gray-400">(নগদ + বিকাশ একসাথে দেওয়া যাবে)</span></label>
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

                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-xl font-medium hover:bg-green-700 transition">পেমেন্ট করুন</button>
                </div>
            </form>
        </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-bold text-gray-900">আইটেম সমূহ</h2>
            </div>
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
                        <th class="px-6 py-3 font-medium">পণ্য</th>
                        <th class="px-6 py-3 font-medium text-center">পরিমাণ</th>
                        <th class="px-6 py-3 font-medium text-right">ইউনিট কস্ট</th>
                        <th class="px-6 py-3 font-medium text-right">ছাড়</th>
                        <th class="px-6 py-3 font-medium text-right">মোট</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($invoice->items as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3">
                            <p class="font-medium text-gray-900">{{ $item->name }}</p>
                            @if($item->sku)<p class="text-xs text-gray-500">SKU: {{ $item->sku }}</p>@endif
                        </td>
                        <td class="px-6 py-3 text-center">{{ $item->quantity }}</td>
                        <td class="px-6 py-3 text-right">৳{{ number_format($item->unit_cost, 2) }}</td>
                        <td class="px-6 py-3 text-right text-red-600">-৳{{ number_format($item->discount, 2) }}</td>
                        <td class="px-6 py-3 text-right font-semibold">৳{{ number_format($item->line_total, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-6 py-10 text-center text-gray-500">কোনো আইটেম নেই</td></tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="4" class="px-6 py-3 text-right text-sm font-medium text-gray-600">সাবটোটাল</td>
                        <td class="px-6 py-3 text-right text-sm font-semibold">৳{{ number_format($invoice->subtotal, 2) }}</td>
                    </tr>
                    @if($invoice->discount_amount > 0)
                    <tr>
                        <td colspan="4" class="px-6 py-3 text-right text-sm font-medium text-gray-600">ডিসকাউন্ট</td>
                        <td class="px-6 py-3 text-right text-sm font-semibold text-red-600">-৳{{ number_format($invoice->discount_amount, 2) }}</td>
                    </tr>
                    @endif
                    @if($invoice->tax_amount > 0)
                    <tr>
                        <td colspan="4" class="px-6 py-3 text-right text-sm font-medium text-gray-600">ট্যাক্স ({{ $invoice->tax_rate }}%)</td>
                        <td class="px-6 py-3 text-right text-sm font-semibold">+৳{{ number_format($invoice->tax_amount, 2) }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td colspan="4" class="px-6 py-3 text-right font-bold text-gray-900">মোট</td>
                        <td class="px-6 py-3 text-right font-bold text-gray-900">৳{{ number_format($invoice->total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-bold text-gray-900">পেমেন্ট ইতিহাস</h2>
            </div>
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
                        <th class="px-6 py-3 font-medium">পেমেন্ট</th>
                        <th class="px-6 py-3 font-medium">তারিখ</th>
                        <th class="px-6 py-3 font-medium">মাধ্যম</th>
                        <th class="px-6 py-3 font-medium">রেফারেন্স</th>
                        <th class="px-6 py-3 font-medium text-right">পরিমাণ</th>
                        <th class="px-6 py-3 font-medium text-right">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($invoice->payments as $payment)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 font-medium text-gray-900">{{ $payment->payment_number }}</td>
                        <td class="px-6 py-3 text-sm text-gray-500">{{ $payment->payment_date->format('d M Y') }}</td>
                        <td class="px-6 py-3 text-sm text-gray-600">{{ $payment->methodName() }}</td>
                        <td class="px-6 py-3 text-sm text-gray-500">{{ $payment->reference ?? '—' }}</td>
                        <td class="px-6 py-3 text-right font-semibold text-green-600">৳{{ number_format($payment->amount, 2) }}</td>
                        <td class="px-6 py-3 text-right">
                            @if($payment->status === 'completed')
                                <form method="POST" action="{{ route('purchase.payments.destroy', $payment) }}" onsubmit="return confirm('পেমেন্ট বাতিল করবেন?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium">বাতিল</button>
                                </form>
                            @else
                                <span class="text-xs text-gray-400">Cancelled</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-6 py-10 text-center text-gray-500">কোনো পেমেন্ট নেই</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoice->notes)
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <h2 class="text-sm font-semibold text-gray-500 uppercase mb-2">নোট</h2>
            <p class="text-gray-700 text-sm whitespace-pre-line">{{ $invoice->notes }}</p>
        </div>
        @endif
    </div>
</div>
@endsection

@include('tenant.purchase.partials._alpine')
