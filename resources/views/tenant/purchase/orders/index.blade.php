@extends('layouts.tenant')

@section('title', __('sidebar.purchase_orders').' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">@lang('sidebar.purchase_orders')</h1>
                    <p class="text-gray-600">সাপ্লায়ারকে অর্ডার করুন এবং রিসিভ ট্র্যাক করুন</p>
                </div>
                <a href="{{ route('purchase.orders.create') }}" class="px-4 py-2 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition">+ নতুন পারচেজ অর্ডার</a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @include('tenant.purchase.partials._nav', ['current' => 'orders'])

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

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">মোট অর্ডার মূল্য (cancelled ছাড়া)</p>
                <p class="text-2xl font-bold text-gray-900">৳{{ number_format($stats['total_value'], 2) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">খোলা অর্ডার</p>
                <p class="text-2xl font-bold text-blue-600">{{ $stats['open'] }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">সম্পূর্ণ রিসিভড</p>
                <p class="text-2xl font-bold text-green-600">{{ $stats['received'] }}</p>
            </div>
        </div>

        <form method="GET" class="bg-white rounded-2xl p-4 shadow-sm mb-6 flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="অর্ডার নম্বর খুঁজুন..." class="flex-1 min-w-[180px] border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 text-sm">
            <select name="status" class="border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 text-sm">
                <option value="">সব স্ট্যাটাস</option>
                @foreach(\App\Models\PurchaseOrder::STATUSES as $key => $label)
                    <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <select name="supplier_id" class="border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 text-sm">
                <option value="">সব সাপ্লায়ার</option>
                @foreach($suppliers as $s)
                    <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
            <input type="date" name="from" value="{{ request('from') }}" class="border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 text-sm">
            <input type="date" name="to" value="{{ request('to') }}" class="border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 text-sm">
            <button type="submit" class="px-5 py-2 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition text-sm">ফিল্টার</button>
            <a href="{{ route('purchase.orders.index') }}" class="px-4 py-2 border rounded-xl text-gray-600 hover:bg-gray-50 transition text-sm">রিসেট</a>
        </form>

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
                        <th class="px-6 py-4 font-medium">অর্ডার</th>
                        <th class="px-6 py-4 font-medium">সাপ্লায়ার</th>
                        <th class="px-6 py-4 font-medium">অর্ডার তারিখ</th>
                        <th class="px-6 py-4 font-medium text-center">রিসিভ</th>
                        <th class="px-6 py-4 font-medium text-right">মোট</th>
                        <th class="px-6 py-4 font-medium">স্ট্যাটাস</th>
                        <th class="px-6 py-4 font-medium text-right">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($orders as $order)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <a href="{{ route('purchase.orders.show', $order) }}" class="font-medium text-purple-600 hover:underline">{{ $order->po_number }}</a>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $order->supplier->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $order->order_date->format('d M Y') }}</td>
                        <td class="px-6 py-4 text-center text-sm">
                            @if(in_array($order->status, ['ordered', 'partially_received']))
                                <span class="font-semibold text-blue-600">{{ $order->totalReceivedQty() }}/{{ $order->items->sum('quantity') }}</span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right font-semibold">৳{{ number_format($order->total, 2) }}</td>
                        <td class="px-6 py-4">@include('tenant.purchase.partials._order-status', ['status' => $order->status])</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if($order->isReceivable())
                                    <a href="{{ route('purchase.receipts.create', ['po_id' => $order->id]) }}" class="text-green-600 hover:text-green-800 text-xs font-medium">Receive</a>
                                @endif
                                @if(in_array($order->status, ['draft', 'ordered', 'partially_received']))
                                    <a href="{{ route('purchase.orders.edit', $order) }}" class="text-purple-600 hover:text-purple-800 text-xs font-medium">Edit</a>
                                @endif
                                <a href="{{ route('purchase.orders.show', $order) }}" class="text-gray-600 hover:text-gray-800 text-xs font-medium">View</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-6 py-12 text-center text-gray-500">কোনো পারচেজ অর্ডার নেই</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($orders->hasPages())<div class="px-6 py-4 border-t">{{ $orders->withQueryString()->links() }}</div>@endif
        </div>
    </div>
</div>
@endsection
