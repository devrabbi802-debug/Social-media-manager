@extends('layouts.tenant')

@section('title', __('orders.order_details').' - #'.$order->order_number.' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ route('orders.index') }}" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">@lang('orders.order_details')</h1>
                        <p class="text-gray-600">#{{ $order->order_number }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('orders.print', $order) }}" target="_blank" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition">@lang('orders.print')</a>
                    <a href="{{ route('orders.edit', $order) }}" class="px-4 py-2 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition">@lang('orders.edit_order')</a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @include('tenant.partials._nav-tabs', ['activePage' => 'orders'])

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left Column --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Order Items --}}
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b">
                        <h2 class="text-lg font-bold text-gray-900">@lang('orders.order_items')</h2>
                    </div>
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 text-left text-sm text-gray-500">
                                <th class="px-6 py-3 font-medium">@lang('orders.product')</th>
                                <th class="px-6 py-3 font-medium">SKU</th>
                                <th class="px-6 py-3 font-medium text-center">@lang('orders.qty')</th>
                                <th class="px-6 py-3 font-medium text-right">@lang('orders.unit_price')</th>
                                <th class="px-6 py-3 font-medium text-right">@lang('orders.total_price')</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($order->items as $item)
                            <tr>
                                <td class="px-6 py-4">
                                    <p class="font-medium text-gray-900">{{ $item->name }}</p>
                                    @if($item->variant)
                                        <p class="text-xs text-gray-500">{{ $item->variant->display ?? $item->variant->name }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $item->sku }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700 text-center">{{ $item->quantity }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700 text-right">৳{{ number_format($item->unit_price, 2) }}</td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900 text-right">৳{{ number_format($item->total_price, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr><td colspan="4" class="px-6 py-2 text-sm text-gray-600 text-right">@lang('orders.subtotal')</td><td class="px-6 py-2 text-sm font-semibold text-right">৳{{ number_format($order->subtotal, 2) }}</td></tr>
                            @if($order->shipping_cost > 0)<tr><td colspan="4" class="px-6 py-2 text-sm text-gray-600 text-right">@lang('orders.shipping_cost')</td><td class="px-6 py-2 text-sm text-right">৳{{ number_format($order->shipping_cost, 2) }}</td></tr>@endif
                            @if($order->tax > 0)<tr><td colspan="4" class="px-6 py-2 text-sm text-gray-600 text-right">@lang('orders.tax')</td><td class="px-6 py-2 text-sm text-right">৳{{ number_format($order->tax, 2) }}</td></tr>@endif
                            @if($order->discount > 0)<tr><td colspan="4" class="px-6 py-2 text-sm text-gray-600 text-right">@lang('orders.discount')</td><td class="px-6 py-2 text-sm text-red-600 text-right">-৳{{ number_format($order->discount, 2) }}</td></tr>@endif
                            <tr class="border-t-2 border-gray-300"><td colspan="4" class="px-6 py-3 text-sm font-bold text-gray-900 text-right">@lang('orders.total')</td><td class="px-6 py-3 text-lg font-bold text-gray-900 text-right">৳{{ number_format($order->total, 2) }}</td></tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Notes --}}
                @if($order->notes)
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h3 class="font-bold text-gray-900 mb-2">@lang('orders.notes')</h3>
                    <p class="text-gray-700 whitespace-pre-wrap">{{ $order->notes }}</p>
                </div>
                @endif
            </div>

            {{-- Right Column --}}
            <div class="space-y-6">
                {{-- Order Status --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h3 class="font-bold text-gray-900 mb-4">@lang('orders.order_status_history')</h3>
                    <div class="space-y-3">
                        @php
                            $statusFlow = ['pending', 'processing', 'shipped', 'delivered'];
                            $currentIdx = array_search($order->status, $statusFlow);
                        @endphp
                        @foreach($statusFlow as $i => $s)
                            <div class="flex items-center gap-3">
                                @if($currentIdx === false || $i < $currentIdx)
                                    <div class="w-6 h-6 rounded-full bg-green-500 flex items-center justify-center"><svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg></div>
                                @elseif($i === $currentIdx)
                                    <div class="w-6 h-6 rounded-full bg-purple-500 flex items-center justify-center"><div class="w-2 h-2 bg-white rounded-full"></div></div>
                                @else
                                    <div class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center"></div>
                                @endif
                                <span class="text-sm {{ $i <= ($currentIdx ?? -1) ? 'text-gray-900 font-medium' : 'text-gray-400' }}">{{ __("orders.{$s}") }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Customer Info --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h3 class="font-bold text-gray-900 mb-4">@lang('orders.customer_info')</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between"><dt class="text-gray-500">@lang('orders.name')</dt><dd class="font-medium text-gray-900">{{ $order->customer_name }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">@lang('orders.phone')</dt><dd class="font-medium text-gray-900">{{ $order->customer_phone }}</dd></div>
                        @if($order->customer && $order->customer->email)
                        <div class="flex justify-between"><dt class="text-gray-500">Email</dt><dd class="font-medium text-gray-900">{{ $order->customer->email }}</dd></div>
                        @endif
                    </dl>
                </div>

                {{-- Payment Info --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h3 class="font-bold text-gray-900 mb-4">@lang('orders.payment_info')</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between"><dt class="text-gray-500">@lang('orders.payment_method')</dt><dd class="font-medium text-gray-900">{{ $order->payment_method ?? '-' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">@lang('orders.payment_status')</dt>
                            <dd>
                                @php $badgeColors = ['paid' => 'bg-green-100 text-green-800', 'pending' => 'bg-yellow-100 text-yellow-800', 'failed' => 'bg-red-100 text-red-800', 'refunded' => 'bg-gray-100 text-gray-800']; @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badgeColors[$order->payment_status] ?? 'bg-gray-100 text-gray-800' }}">{{ __("orders.{$order->payment_status}") }}</span>
                            </dd>
                        </div>
                    </dl>
                </div>

                {{-- Shipping Info --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h3 class="font-bold text-gray-900 mb-4">@lang('orders.shipping_info')</h3>
                    <dl class="space-y-2 text-sm">
                        @if($order->shippingAddress)
                            <div><dt class="text-gray-500">Address</dt><dd class="font-medium text-gray-900">{{ $order->shippingAddress->address }}, {{ $order->shippingAddress->city }}, {{ $order->shippingAddress->district }}</dd></div>
                        @endif
                        <div class="flex justify-between"><dt class="text-gray-500">@lang('orders.carrier')</dt><dd class="font-medium text-gray-900">{{ $order->carrier ?? '-' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">@lang('orders.tracking_id')</dt><dd class="font-medium text-gray-900">{{ $order->tracking_id ?? '-' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">@lang('orders.estimated_delivery')</dt><dd class="font-medium text-gray-900">{{ $order->estimated_delivery ? $order->estimated_delivery->format('d M, Y') : '-' }}</dd></div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
