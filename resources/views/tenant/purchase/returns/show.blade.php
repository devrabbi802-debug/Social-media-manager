@extends('layouts.tenant')

@section('title', $return->return_number.' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $return->return_number }}</h1>
                    <p class="text-gray-600">
                        @if($return->status === 'completed')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Completed</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Cancelled</span>
                        @endif
                    </p>
                </div>
                <div class="flex gap-2 flex-wrap">
                    @if($return->purchaseReceipt)
                        <a href="{{ route('purchase.receipts.show', $return->purchaseReceipt) }}" class="px-4 py-2 bg-white border rounded-xl text-gray-600 hover:bg-gray-50 transition">রিসিপ্ট দেখুন</a>
                    @endif
                    @if($return->status === 'completed')
                        <form method="POST" action="{{ route('purchase.returns.cancel', $return) }}" onsubmit="return confirm('রিটার্ন বাতিল করলে স্টক ফিরে আসবে। নিশ্চিত?');">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-white text-red-600 border border-red-200 rounded-xl font-medium hover:bg-red-50 transition">বাতিল</button>
                        </form>
                    @endif
                    <a href="{{ route('purchase.returns.index') }}" class="px-4 py-2 border rounded-xl text-gray-600 hover:bg-gray-50 transition">← Back</a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        @include('tenant.purchase.partials._nav', ['current' => 'returns'])

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
                <a href="{{ route('purchase.suppliers.show', $return->supplier) }}" class="text-lg font-bold text-purple-600 hover:underline">{{ $return->supplier->name }}</a>
                @if($return->supplier->company)<p class="text-gray-600 text-sm">{{ $return->supplier->company }}</p>@endif
                @if($return->supplier->phone)<p class="text-gray-600 text-sm">📞 {{ $return->supplier->phone }}</p>@endif
                @if($return->supplier->address)<p class="text-gray-600 text-sm mt-2">{{ $return->supplier->address }}</p>@endif
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h2 class="text-sm font-semibold text-gray-500 uppercase mb-3">রিটার্ন তথ্য</h2>
                <div class="space-y-1 text-sm">
                    <p class="text-gray-600">তারিখ: <span class="font-medium text-gray-900">{{ $return->return_date->format('d M Y') }}</span></p>
                    <p class="text-gray-600">গুদাম: <span class="font-medium text-gray-900">{{ $return->warehouse?->name ?? '—' }}</span></p>
                    @if($return->purchaseReceipt)
                        <p class="text-gray-600">রিসিপ্ট: <a href="{{ route('purchase.receipts.show', $return->purchaseReceipt) }}" class="font-medium text-purple-600 hover:underline">{{ $return->purchaseReceipt->receipt_number }}</a></p>
                    @endif
                    @if($return->creator)<p class="text-gray-600">তৈরি করেছেন: <span class="font-medium text-gray-900">{{ $return->creator->name }}</span></p>@endif
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6 border-l-4 border-red-400">
                <h2 class="text-sm font-semibold text-gray-500 uppercase mb-3">ফেরত সারাংশ</h2>
                <div class="flex justify-between text-base font-bold text-gray-900">
                    <span>মোট ফেরত</span>
                    <span class="text-red-600">৳{{ number_format($return->total, 2) }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-bold text-gray-900">ফেরত দেওয়া আইটেম</h2>
            </div>
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
                        <th class="px-6 py-3 font-medium">পণ্য</th>
                        <th class="px-6 py-3 font-medium text-center">পরিমাণ</th>
                        <th class="px-6 py-3 font-medium text-right">ইউনিট কস্ট</th>
                        <th class="px-6 py-3 font-medium text-right">মোট</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($return->items as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3">
                            <p class="font-medium text-gray-900">{{ $item->name }}</p>
                            @if($item->sku)<p class="text-xs text-gray-500">SKU: {{ $item->sku }}</p>@endif
                        </td>
                        <td class="px-6 py-3 text-center">{{ $item->quantity }}</td>
                        <td class="px-6 py-3 text-right">৳{{ number_format($item->unit_cost, 2) }}</td>
                        <td class="px-6 py-3 text-right font-semibold">৳{{ number_format($item->line_total, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-6 py-10 text-center text-gray-500">কোনো আইটেম নেই</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($return->reason)
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <h2 class="text-sm font-semibold text-gray-500 uppercase mb-2">রিটার্নের কারণ</h2>
            <p class="text-gray-700 text-sm">{{ $return->reason }}</p>
        </div>
        @endif
    </div>
</div>
@endsection
