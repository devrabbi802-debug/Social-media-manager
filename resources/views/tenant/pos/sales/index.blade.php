@extends('layouts.tenant')

@section('title', __('POS Sales') . ' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">POS Sales</h1>
                    <p class="text-gray-600">সকল পয়েন্ট অফ সেল বিক্রয়</p>
                </div>
                <a href="{{ route('pos.index') }}" class="px-4 py-2 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition">
                    + New Sale
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @include('tenant.partials._nav-tabs', ['activePage' => 'pos'])

        {{-- Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">মোট বিক্রয় (completed)</p>
                <p class="text-2xl font-bold text-purple-600">৳{{ number_format($totalSales, 2) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">মোট অর্ডার</p>
                <p class="text-2xl font-bold text-gray-900">{{ $totalOrders }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">মোট রিফান্ড</p>
                <p class="text-2xl font-bold text-red-600">৳{{ number_format($totalRefunds, 2) }}</p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <input type="text" name="order_number" value="{{ request('order_number') }}" placeholder="অর্ডার নম্বর" class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <select name="status" class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                        <option value="">সব স্ট্যাটাস</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                        <option value="hold" {{ request('status') === 'hold' ? 'selected' : '' }}>Hold</option>
                    </select>
                </div>
                <div>
                    <select name="payment_method" class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                        <option value="">সব পেমেন্ট</option>
                        <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="card" {{ request('payment_method') === 'card' ? 'selected' : '' }}>Card</option>
                        <option value="mobile" {{ request('payment_method') === 'mobile' ? 'selected' : '' }}>Mobile</option>
                    </select>
                </div>
                <div>
                    <input type="date" name="from" value="{{ request('from') }}" class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm">
                </div>
                <div>
                    <input type="date" name="to" value="{{ request('to') }}" class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm">
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">অর্ডার</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">কাস্টমার</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ক্যাশিয়ার</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">পেমেন্ট</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">মোট</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">স্ট্যাটাস</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">তারিখ</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($orders as $order)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $order->order_number }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ $order->customer_name ?? 'ওয়াক-ইন' }}
                                    @if($order->customer_phone)
                                        <span class="text-gray-400 text-xs">{{ $order->customer_phone }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $order->user->name ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $order->payment_method }}</td>
                                <td class="px-6 py-4 text-sm font-bold text-gray-900">৳{{ number_format($order->total, 2) }}</td>
                                <td class="px-6 py-4">
                                    @php
                                        $badge = match($order->status) {
                                            'completed' => 'bg-green-100 text-green-700',
                                            'refunded' => 'bg-red-100 text-red-700',
                                            'hold' => 'bg-amber-100 text-amber-700',
                                            default => 'bg-gray-100 text-gray-700',
                                        };
                                    @endphp
                                    <span class="px-2 py-1 text-xs rounded-full font-medium {{ $badge }}">{{ $order->status }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $order->created_at->format('d M Y H:i') }}</td>
                                <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                                    <a href="{{ route('pos.sales.show', $order) }}" class="text-purple-600 hover:text-purple-800 text-sm">View</a>
                                    <a href="{{ route('pos.sales.receipt', $order) }}" class="text-blue-600 hover:text-blue-800 text-sm">Receipt</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-10 text-center text-gray-500">কোনো বিক্রয় পাওয়া যায়নি</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
