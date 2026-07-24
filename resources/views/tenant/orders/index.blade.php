@extends('layouts.tenant')

@section('title', __('orders.title').' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">@lang('orders.title')</h1>
                    <p class="text-gray-600">@lang('orders.subtitle')</p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('orders.export', request()->only(['status', 'date_from', 'date_to'])) }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            @lang('orders.export')
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @include('tenant.partials._nav-tabs', ['activePage' => 'orders'])

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">@lang('orders.total_orders')</p>
                <p class="text-2xl font-bold text-gray-900">{{ $totalOrders }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">@lang('orders.pending')</p>
                <p class="text-2xl font-bold text-yellow-600">{{ $pendingCount }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">@lang('orders.processing')</p>
                <p class="text-2xl font-bold text-blue-600">{{ $processingCount }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">@lang('orders.delivered')</p>
                <p class="text-2xl font-bold text-green-600">{{ $deliveredCount }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">@lang('orders.cancelled')</p>
                <p class="text-2xl font-bold text-red-600">{{ $cancelledCount }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">@lang('orders.total_revenue')</p>
                <p class="text-2xl font-bold text-purple-600">৳{{ number_format($totalRevenue, 2) }}</p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-7 gap-4">
                <div class="md:col-span-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('orders.search_placeholder') }}" class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <select name="status" class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                        <option value="">@lang('orders.all_statuses')</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>@lang('orders.pending')</option>
                        <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>@lang('orders.processing')</option>
                        <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>@lang('orders.shipped')</option>
                        <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>@lang('orders.delivered')</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>@lang('orders.cancelled')</option>
                        <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>@lang('orders.refunded')</option>
                    </select>
                </div>
                <div>
                    <select name="payment_status" class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                        <option value="">@lang('orders.all_payment_statuses')</option>
                        <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>@lang('orders.pending')</option>
                        <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>@lang('orders.paid')</option>
                        <option value="failed" {{ request('payment_status') === 'failed' ? 'selected' : '' }}>@lang('orders.failed')</option>
                        <option value="refunded" {{ request('payment_status') === 'refunded' ? 'selected' : '' }}>@lang('orders.refunded')</option>
                    </select>
                </div>
                <div>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-purple-600 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-purple-700 transition">@lang('orders.filter')</button>
                    <a href="{{ route('orders.index') }}" class="flex-1 bg-gray-100 text-gray-700 px-4 py-2 rounded-xl text-sm font-medium hover:bg-gray-200 transition text-center">@lang('orders.reset')</a>
                </div>
            </form>
        </div>

        {{-- Bulk Update Form --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm mb-6" x-data="{ showBulk: false, selectedIds: [] }">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-900">@lang('orders.order_management')</h2>
                <button @click="showBulk = !showBulk" class="text-sm text-purple-600 hover:text-purple-700 font-medium">
                    <span x-show="!showBulk">@lang('orders.bulk_update')</span>
                    <span x-show="showBulk">@lang('orders.cancel')</span>
                </button>
            </div>

            <form action="{{ route('orders.bulk-update') }}" method="POST" x-show="showBulk" class="mb-4 p-4 bg-purple-50 rounded-xl border border-purple-200">
                @csrf
                <div class="flex items-center gap-4">
                    <p class="text-sm text-gray-700">@lang('orders.select_orders')</p>
                    <template x-for="id in selectedIds">
                        <input type="hidden" name="order_ids[]" :value="id">
                    </template>
                    <select name="status" required class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
                        <option value="">@lang('orders.select_status')</option>
                        <option value="pending">@lang('orders.pending')</option>
                        <option value="processing">@lang('orders.processing')</option>
                        <option value="shipped">@lang('orders.shipped')</option>
                        <option value="delivered">@lang('orders.delivered')</option>
                        <option value="cancelled">@lang('orders.cancelled')</option>
                        <option value="refunded">@lang('orders.refunded')</option>
                    </select>
                    <button type="submit" class="bg-purple-600 text-white px-4 py-1.5 rounded-lg text-sm font-medium hover:bg-purple-700">@lang('orders.apply')</button>
                </div>
            </form>

            {{-- Orders Table --}}
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 text-left text-sm text-gray-500">
                            <th class="px-4 py-3 w-10">
                                <input type="checkbox" @click="selectedIds = $event.target.checked ? {{ $orders->pluck('id') }} : []" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                            </th>
                            <th class="px-4 py-3 font-medium">
                                <a href="{{ route('orders.index', array_merge(request()->except(['sort', 'dir']), ['sort' => 'order_number', 'dir' => request('sort') === 'order_number' && request('dir') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center gap-1 hover:text-gray-700">
                                    @lang('orders.order_number')
                                    @if(request('sort') === 'order_number') <span class="text-xs">{{ request('dir') === 'asc' ? '↑' : '↓' }}</span> @endif
                                </a>
                            </th>
                            <th class="px-4 py-3 font-medium">@lang('orders.customer')</th>
                            <th class="px-4 py-3 font-medium">@lang('orders.phone')</th>
                            <th class="px-4 py-3 font-medium">
                                <a href="{{ route('orders.index', array_merge(request()->except(['sort', 'dir']), ['sort' => 'total', 'dir' => request('sort') === 'total' && request('dir') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center gap-1 hover:text-gray-700">
                                    @lang('orders.total')
                                    @if(request('sort') === 'total') <span class="text-xs">{{ request('dir') === 'asc' ? '↑' : '↓' }}</span> @endif
                                </a>
                            </th>
                            <th class="px-4 py-3 font-medium">@lang('orders.status')</th>
                            <th class="px-4 py-3 font-medium">@lang('orders.payment_status')</th>
                            <th class="px-4 py-3 font-medium">@lang('orders.items')</th>
                            <th class="px-4 py-3 font-medium">
                                <a href="{{ route('orders.index', array_merge(request()->except(['sort', 'dir']), ['sort' => 'created_at', 'dir' => request('sort') === 'created_at' && request('dir') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center gap-1 hover:text-gray-700">
                                    @lang('orders.date')
                                    @if(request('sort') === 'created_at' || !request('sort')) <span class="text-xs">{{ request('dir', 'desc') === 'asc' ? '↑' : '↓' }}</span> @endif
                                </a>
                            </th>
                            <th class="px-4 py-3 font-medium text-right">@lang('orders.actions')</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($orders as $order)
                        <tr class="hover:bg-gray-50" x-data="{ checked: false }">
                            <td class="px-4 py-3">
                                <input type="checkbox" x-model="checked" @change="checked ? selectedIds.push({{ $order->id }}) : selectedIds.splice(selectedIds.indexOf({{ $order->id }}), 1)" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('orders.show', $order) }}" class="font-medium text-gray-900 hover:text-purple-600">#{{ $order->order_number }}</a>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $order->customer_name ?? ($order->customer->name ?? '-') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $order->customer_phone }}</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">৳{{ number_format($order->total, 2) }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $statusColors = ['pending' => 'bg-yellow-100 text-yellow-800', 'processing' => 'bg-blue-100 text-blue-800', 'shipped' => 'bg-indigo-100 text-indigo-800', 'delivered' => 'bg-green-100 text-green-800', 'cancelled' => 'bg-red-100 text-red-800', 'refunded' => 'bg-gray-100 text-gray-800'];
                                    $color = $statusColors[$order->status] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $color }}">
                                    {{ __("orders.{$order->status}") }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $psColors = ['paid' => 'text-green-600', 'pending' => 'text-yellow-600', 'failed' => 'text-red-600', 'refunded' => 'text-gray-600'];
                                @endphp
                                <span class="text-sm font-medium {{ $psColors[$order->payment_status] ?? 'text-gray-600' }}">
                                    {{ __("orders.{$order->payment_status}") }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $order->items->count() }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $order->created_at->format('d M, Y') }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('orders.show', $order) }}" class="text-purple-600 hover:text-purple-700 text-sm font-medium">@lang('common.view')</a>
                                    <a href="{{ route('orders.edit', $order) }}" class="text-gray-500 hover:text-gray-700 text-sm">@lang('common.edit')</a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="10" class="px-6 py-12 text-center text-gray-500">@lang('orders.no_orders')</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($orders->hasPages())<div class="px-4 py-4 border-t">{{ $orders->withQueryString()->links() }}</div>@endif
        </div>
    </div>
</div>
@endsection
