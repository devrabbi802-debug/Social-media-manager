@extends('layouts.tenant')

@section('title', $receipt->receipt_number.' - SocialBoost AI')

@section('content')
@php $linkedInvoice = $receipt->invoice->first(); @endphp
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $receipt->receipt_number }}</h1>
                    <p class="text-gray-600">মাল রিসিপ্ট (GRN)</p>
                </div>
                <div class="flex gap-2 flex-wrap">
                    @if($receipt->purchaseOrder)
                        <a href="{{ route('purchase.orders.show', $receipt->purchaseOrder) }}" class="px-4 py-2 bg-white border rounded-xl text-gray-600 hover:bg-gray-50 transition">অর্ডার দেখুন</a>
                    @endif
                    @if(! $linkedInvoice)
                        <a href="{{ route('purchase.invoices.create', ['receipt_id' => $receipt->id]) }}" class="px-4 py-2 bg-green-600 text-white rounded-xl font-medium hover:bg-green-700 transition">+ বিল তৈরি করুন</a>
                    @endif
                    <a href="{{ route('purchase.receipts.print', $receipt) }}" target="_blank" class="px-4 py-2 bg-white border rounded-xl text-gray-600 hover:bg-gray-50 transition">Print</a>
                    <form method="POST" action="{{ route('purchase.receipts.destroy', $receipt) }}" onsubmit="return confirm('রিসিপ্ট ডিলিট করলে স্টক ফিরে যাবে। নিশ্চিত?' + ($linkedInvoice ? ' সতর্কতা: এতে বিল লিংক আছে!' : ''));">
                        @csrf @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-white text-red-600 border border-red-200 rounded-xl font-medium hover:bg-red-50 transition">Delete</button>
                    </form>
                    <a href="{{ route('purchase.receipts.index') }}" class="px-4 py-2 border rounded-xl text-gray-600 hover:bg-gray-50 transition">← Back</a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        @include('tenant.purchase.partials._nav', ['current' => 'receipts'])

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
                <a href="{{ route('purchase.suppliers.show', $receipt->supplier) }}" class="text-lg font-bold text-purple-600 hover:underline">{{ $receipt->supplier->name }}</a>
                @if($receipt->supplier->company)<p class="text-gray-600 text-sm">{{ $receipt->supplier->company }}</p>@endif
                @if($receipt->supplier->phone)<p class="text-gray-600 text-sm">📞 {{ $receipt->supplier->phone }}</p>@endif
                @if($receipt->supplier->address)<p class="text-gray-600 text-sm mt-2">{{ $receipt->supplier->address }}</p>@endif
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h2 class="text-sm font-semibold text-gray-500 uppercase mb-3">রিসিপ্ট তথ্য</h2>
                <div class="space-y-1 text-sm">
                    <p class="text-gray-600">তারিখ: <span class="font-medium text-gray-900">{{ $receipt->receipt_date->format('d M Y') }}</span></p>
                    <p class="text-gray-600">গুদাম: <span class="font-medium text-gray-900">{{ $receipt->warehouse?->name ?? '—' }}</span></p>
                    @if($receipt->purchaseOrder)
                        <p class="text-gray-600">অর্ডার: <a href="{{ route('purchase.orders.show', $receipt->purchaseOrder) }}" class="font-medium text-purple-600 hover:underline">{{ $receipt->purchaseOrder->po_number }}</a></p>
                    @endif
                    @if($receipt->creator)<p class="text-gray-600">রিসিভ করেছেন: <span class="font-medium text-gray-900">{{ $receipt->creator->name }}</span></p>@endif
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h2 class="text-sm font-semibold text-gray-500 uppercase mb-3">সারাংশ</h2>
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between text-gray-600"><span>সাবটোটাল</span><span class="font-medium text-gray-900">৳{{ number_format($receipt->subtotal, 2) }}</span></div>
                    @if($receipt->tax_amount > 0)
                        <div class="flex justify-between text-gray-600"><span>ট্যাক্স ({{ $receipt->tax_rate }}%)</span><span class="font-medium text-gray-900">+৳{{ number_format($receipt->tax_amount, 2) }}</span></div>
                    @endif
                    <div class="flex justify-between text-base font-bold text-gray-900 pt-2 border-t"><span>মোট</span><span>৳{{ number_format($receipt->total, 2) }}</span></div>
                </div>
                @if($linkedInvoice)
                    <div class="mt-4 pt-4 border-t">
                        <a href="{{ route('purchase.invoices.show', $linkedInvoice) }}" class="text-sm font-medium text-green-600 hover:text-green-800">→ বিল: {{ $linkedInvoice->invoice_number }}</a>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-bold text-gray-900">রিসিভ করা আইটেম</h2>
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
                    @forelse($receipt->items as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3">
                            <p class="font-medium text-gray-900">{{ $item->name }}</p>
                            @if($item->sku)<p class="text-xs text-gray-500">SKU: {{ $item->sku }}</p>@endif
                        </td>
                        <td class="px-6 py-3 text-center">{{ $item->quantity }}</td>
                        <td class="px-6 py-3 text-right">৳{{ number_format($item->unit_cost, 2) }}</td>
                        <td class="px-6 py-3 text-right">৳{{ number_format($item->discount, 2) }}</td>
                        <td class="px-6 py-3 text-right font-semibold">৳{{ number_format($item->line_total, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-6 py-10 text-center text-gray-500">কোনো আইটেম নেই</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($receipt->notes)
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <h2 class="text-sm font-semibold text-gray-500 uppercase mb-2">নোট</h2>
            <p class="text-gray-700 text-sm">{{ $receipt->notes }}</p>
        </div>
        @endif
    </div>
</div>
@endsection
