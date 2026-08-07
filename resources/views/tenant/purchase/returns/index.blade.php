@extends('layouts.tenant')

@section('title', __('sidebar.purchase_returns').' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">@lang('sidebar.purchase_returns')</h1>
                    <p class="text-gray-600">সাপ্লায়ারকে মাল ফেরত — স্টক আউট হয় এখান থেকে</p>
                </div>
                <a href="{{ route('purchase.returns.create') }}" class="px-4 py-2 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition">+ নতুন রিটার্ন</a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @include('tenant.purchase.partials._nav', ['current' => 'returns'])

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
            <input type="text" name="search" value="{{ request('search') }}" placeholder="রিটার্ন / সাপ্লায়ার খুঁজুন..." class="flex-1 min-w-[180px] border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 text-sm">
            <select name="status" class="border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 text-sm">
                <option value="">সব স্ট্যাটাস</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
            <input type="date" name="from" value="{{ request('from') }}" class="border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 text-sm">
            <input type="date" name="to" value="{{ request('to') }}" class="border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 text-sm">
            <button type="submit" class="px-5 py-2 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition text-sm">ফিল্টার</button>
            <a href="{{ route('purchase.returns.index') }}" class="px-4 py-2 border rounded-xl text-gray-600 hover:bg-gray-50 transition text-sm">রিসেট</a>
        </form>

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
                        <th class="px-6 py-4 font-medium">রিটার্ন</th>
                        <th class="px-6 py-4 font-medium">সাপ্লায়ার</th>
                        <th class="px-6 py-4 font-medium">রিসিপ্ট</th>
                        <th class="px-6 py-4 font-medium">তারিখ</th>
                        <th class="px-6 py-4 font-medium">কারণ</th>
                        <th class="px-6 py-4 font-medium text-right">মোট</th>
                        <th class="px-6 py-4 font-medium">স্ট্যাটাস</th>
                        <th class="px-6 py-4 font-medium text-right">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($returns as $return)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4"><a href="{{ route('purchase.returns.show', $return) }}" class="font-medium text-purple-600 hover:underline">{{ $return->return_number }}</a></td>
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('purchase.suppliers.show', $return->supplier) }}" class="text-purple-600 hover:underline">{{ $return->supplier->name }}</a>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @if($return->purchaseReceipt)
                                <a href="{{ route('purchase.receipts.show', $return->purchaseReceipt) }}" class="text-purple-600 hover:underline">{{ $return->purchaseReceipt->receipt_number }}</a>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $return->return_date->format('d M Y') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600 max-w-[200px] truncate">{{ $return->reason ?? '—' }}</td>
                        <td class="px-6 py-4 text-right font-semibold">৳{{ number_format($return->total, 2) }}</td>
                        <td class="px-6 py-4">
                            @if($return->status === 'completed')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Completed</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Cancelled</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if($return->status === 'completed')
                                    <form method="POST" action="{{ route('purchase.returns.cancel', $return) }}" onsubmit="return confirm('রিটার্ন বাতিল করলে স্টক ফিরে আসবে। নিশ্চিত?');">
                                        @csrf
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium">বাতিল</button>
                                    </form>
                                @endif
                                <a href="{{ route('purchase.returns.show', $return) }}" class="text-purple-600 hover:text-purple-800 text-xs font-medium">View</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-6 py-12 text-center text-gray-500">কোনো রিটার্ন নেই</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($returns->hasPages())<div class="px-6 py-4 border-t">{{ $returns->withQueryString()->links() }}</div>@endif
        </div>
    </div>
</div>
@endsection
