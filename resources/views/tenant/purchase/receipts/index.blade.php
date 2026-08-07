@extends('layouts.tenant')

@section('title', __('sidebar.purchase_receipts').' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">@lang('sidebar.purchase_receipts')</h1>
                    <p class="text-gray-600">মাল রিসিভ (GRN) — স্টক ইন করা হয় এখান থেকে</p>
                </div>
                <a href="{{ route('purchase.receipts.create') }}" class="px-4 py-2 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition">+ নতুন রিসিভ</a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @include('tenant.purchase.partials._nav', ['current' => 'receipts'])

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-2xl p-4 mb-6 flex items-center">
            <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <p class="text-green-800 font-medium">{{ session('success') }}</p>
        </div>
        @endif

        @if(session('error'))
        <div class="bg-red-50 border border-red-200 rounded-2xl p-4 mb-6 flex items-center">
            <svg class="w-6 h-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            <p class="text-red-800 font-medium">{{ session('error') }}</p>
        </div>
        @endif

        <form method="GET" class="bg-white rounded-2xl p-4 shadow-sm mb-6 flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="রিসিপ্ট নম্বর খুঁজুন..." class="flex-1 min-w-[180px] border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 text-sm">
            <select name="supplier_id" class="border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 text-sm">
                <option value="">সব সাপ্লায়ার</option>
                @foreach($suppliers as $s)
                    <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
            <input type="date" name="from" value="{{ request('from') }}" class="border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 text-sm">
            <input type="date" name="to" value="{{ request('to') }}" class="border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 text-sm">
            <button type="submit" class="px-5 py-2 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition text-sm">ফিল্টার</button>
            <a href="{{ route('purchase.receipts.index') }}" class="px-4 py-2 border rounded-xl text-gray-600 hover:bg-gray-50 transition text-sm">রিসেট</a>
        </form>

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
                        <th class="px-6 py-4 font-medium">রিসিপ্ট</th>
                        <th class="px-6 py-4 font-medium">সাপ্লায়ার</th>
                        <th class="px-6 py-4 font-medium">পারচেজ অর্ডার</th>
                        <th class="px-6 py-4 font-medium">তারিখ</th>
                        <th class="px-6 py-4 font-medium">গুদাম</th>
                        <th class="px-6 py-4 font-medium text-right">মোট</th>
                        <th class="px-6 py-4 font-medium text-right">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($receipts as $receipt)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <a href="{{ route('purchase.receipts.show', $receipt) }}" class="font-medium text-purple-600 hover:underline">{{ $receipt->receipt_number }}</a>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $receipt->supplier->name }}</td>
                        <td class="px-6 py-4 text-sm">
                            @if($receipt->purchaseOrder)
                                <a href="{{ route('purchase.orders.show', $receipt->purchaseOrder) }}" class="text-purple-600 hover:underline">{{ $receipt->purchaseOrder->po_number }}</a>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $receipt->receipt_date->format('d M Y') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $receipt->warehouse?->name ?? '—' }}</td>
                        <td class="px-6 py-4 text-right font-semibold">৳{{ number_format($receipt->total, 2) }}</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if($receipt->invoice->isEmpty())
                                    <a href="{{ route('purchase.invoices.create', ['receipt_id' => $receipt->id]) }}" class="text-green-600 hover:text-green-800 text-xs font-medium">বিল করুন</a>
                                @endif
                                <a href="{{ route('purchase.receipts.print', $receipt) }}" target="_blank" class="text-gray-600 hover:text-gray-800 text-xs font-medium">Print</a>
                                <a href="{{ route('purchase.receipts.show', $receipt) }}" class="text-purple-600 hover:text-purple-800 text-xs font-medium">View</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-6 py-12 text-center text-gray-500">কোনো রিসিপ্ট নেই</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($receipts->hasPages())<div class="px-6 py-4 border-t">{{ $receipts->withQueryString()->links() }}</div>@endif
        </div>
    </div>
</div>
@endsection
