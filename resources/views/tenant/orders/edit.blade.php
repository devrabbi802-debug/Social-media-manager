@extends('layouts.tenant')

@section('title', __('orders.edit_order').' - #'.$order->order_number.' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ route('orders.show', $order) }}" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">@lang('orders.edit_order')</h1>
                        <p class="text-gray-600">#{{ $order->order_number }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form action="{{ route('orders.update', $order) }}" method="POST" class="max-w-3xl">
            @csrf
            @method('PUT')

            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm">{{ session('success') }}</div>
            @endif

            {{-- Status & Payment --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm mb-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">@lang('orders.order_summary')</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">@lang('orders.status')</label>
                        <select name="status" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-purple-500">
                            @foreach($statuses as $s)
                                <option value="{{ $s }}" {{ $order->status === $s ? 'selected' : '' }}>{{ __("orders.{$s}") }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">@lang('orders.payment_status')</label>
                        <select name="payment_status" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-purple-500">
                            @foreach($paymentStatuses as $ps)
                                <option value="{{ $ps }}" {{ $order->payment_status === $ps ? 'selected' : '' }}>{{ __("orders.{$ps}") }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Tracking --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm mb-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">@lang('orders.tracking_info')</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">@lang('orders.carrier')</label>
                        <input type="text" name="carrier" value="{{ old('carrier', $order->carrier) }}" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">@lang('orders.tracking_id')</label>
                        <input type="text" name="tracking_id" value="{{ old('tracking_id', $order->tracking_id) }}" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">@lang('orders.estimated_delivery')</label>
                        <input type="date" name="estimated_delivery" value="{{ old('estimated_delivery', $order->estimated_delivery?->format('Y-m-d')) }}" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm mb-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">@lang('orders.notes')</h2>
                <textarea name="notes" rows="4" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-purple-500">{{ old('notes', $order->notes) }}</textarea>
            </div>

            {{-- Totals (Read-only) --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm mb-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">@lang('orders.order_summary')</h2>
                <dl class="space-y-2 text-sm divide-y">
                    <div class="flex justify-between py-2"><dt class="text-gray-500">@lang('orders.subtotal')</dt><dd class="font-semibold">৳{{ number_format($order->subtotal, 2) }}</dd></div>
                    @if($order->shipping_cost > 0)<div class="flex justify-between py-2"><dt class="text-gray-500">@lang('orders.shipping_cost')</dt><dd class="font-semibold">৳{{ number_format($order->shipping_cost, 2) }}</dd></div>@endif
                    @if($order->tax > 0)<div class="flex justify-between py-2"><dt class="text-gray-500">@lang('orders.tax')</dt><dd class="font-semibold">৳{{ number_format($order->tax, 2) }}</dd></div>@endif
                    @if($order->discount > 0)<div class="flex justify-between py-2"><dt class="text-gray-500">@lang('orders.discount')</dt><dd class="font-semibold text-red-600">-৳{{ number_format($order->discount, 2) }}</dd></div>@endif
                    <div class="flex justify-between py-2 text-base"><dt class="font-bold text-gray-900">@lang('orders.total')</dt><dd class="font-bold text-gray-900">৳{{ number_format($order->total, 2) }}</dd></div>
                </dl>
            </div>

            <div class="flex items-center gap-4">
                <button type="submit" class="px-6 py-3 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition">@lang('orders.save')</button>
                <a href="{{ route('orders.show', $order) }}" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition">@lang('orders.cancel')</a>
            </div>
        </form>
    </div>
</div>
@endsection
