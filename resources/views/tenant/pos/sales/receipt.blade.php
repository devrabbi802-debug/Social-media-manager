@extends('layouts.tenant')

@section('title', 'Receipt - SocialBoost AI')

@push('styles')
<style>
    @media print {
        body * { visibility: hidden; }
        #receipt, #receipt * { visibility: visible; }
        #receipt { position: absolute; left: 0; top: 0; width: 100%; box-shadow: none; }
        .no-print { display: none !important; }
    }
    .receipt-mono { font-family: 'Courier New', monospace; }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-100 p-4">
    <div class="no-print max-w-xl mx-auto mb-4 flex gap-3">
        <button onclick="window.print()" class="px-5 py-2.5 bg-purple-600 text-white rounded-xl font-semibold hover:bg-purple-700">🖨️ Print Receipt</button>
        <a href="{{ route('pos.sales.show', $order) }}" class="px-5 py-2.5 border border-gray-300 bg-white text-gray-700 rounded-xl font-semibold hover:bg-gray-50">Back to Sale</a>
        <a href="{{ route('pos.index') }}" class="px-5 py-2.5 border border-gray-300 bg-white text-gray-700 rounded-xl font-semibold hover:bg-gray-50">+ New Sale</a>
    </div>

    @if(session('success'))
        <div class="no-print max-w-xl mx-auto mb-4 bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div id="receipt" class="max-w-xl mx-auto bg-white shadow-lg rounded-xl p-6">
        <div class="receipt-mono text-sm text-gray-800">
            <div class="text-center">
                @if($companyLogoUrl)
                    <img src="{{ $companyLogoUrl }}" alt="{{ $companyName ?? $settings->store_name ?? 'My Store' }}"
                         class="mx-auto mb-2 w-36 h-36 object-contain">
                @endif
                <h1 class="text-xl font-bold">{{ $companyName ?? $settings->store_name ?? 'My Store' }}</h1>
                @if($settings->store_phone)<p>{{ $settings->store_phone }}</p>@endif
                @if($settings->store_address)<p>{{ $settings->store_address }}</p>@endif
            </div>

            <div class="border-t border-dashed border-gray-400 my-3"></div>

            <div class="space-y-1">
                <div class="flex justify-between"><span>Order #</span><span>{{ $order->order_number }}</span></div>
                <div class="flex justify-between"><span>Date</span><span>{{ $order->created_at->format('d M Y H:i') }}</span></div>
                <div class="flex justify-between"><span>Cashier</span><span>{{ $order->user->name ?? '-' }}</span></div>
                @if($order->customer_name)
                    <div class="flex justify-between"><span>Customer</span><span>{{ $order->customer_name }} {{ $order->customer_phone ? '('.$order->customer_phone.')' : '' }}</span></div>
                @endif
            </div>

            <div class="border-t border-dashed border-gray-400 my-3"></div>

            <table class="w-full">
                <thead>
                    <tr class="font-bold">
                        <td class="py-1">Item</td>
                        <td class="text-right">Qty</td>
                        <td class="text-right">Price</td>
                        <td class="text-right">Total</td>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td class="py-1 pr-2 break-words whitespace-normal">{{ $item->name }}</td>
                            <td class="text-right">{{ $item->quantity }}</td>
                            <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-right font-semibold">{{ number_format($item->total_price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="border-t border-dashed border-gray-400 my-3"></div>

            <div class="space-y-1">
                <div class="flex justify-between"><span>Subtotal</span><span>{{ number_format($order->subtotal, 2) }}</span></div>
                @if($order->discount_amount > 0)
                    <div class="flex justify-between"><span>Discount</span><span>- {{ number_format($order->discount_amount, 2) }}</span></div>
                @endif
                @if($order->tax_amount > 0)
                    <div class="flex justify-between"><span>Tax ({{ $order->tax_rate }}% {{ $order->tax_type }})</span><span>{{ number_format($order->tax_amount, 2) }}</span></div>
                @endif
                <div class="flex justify-between text-lg font-bold pt-1"><span>TOTAL</span><span>{{ $settings->currency_symbol ?? '৳' }}{{ number_format($order->total, 2) }}</span></div>
                @if($order->isCashPayment())
                    <div class="flex justify-between"><span>Tendered</span><span>{{ number_format($order->tendered_amount, 2) }}</span></div>
                    <div class="flex justify-between"><span>Change</span><span>{{ number_format($order->change_due, 2) }}</span></div>
                @endif
                <div class="flex justify-between"><span>Payment</span><span>{{ $order->paymentMethodName() }} ({{ $order->payment_status }})</span></div>
            </div>

            @if($order->refunds->count())
                <div class="border-t border-dashed border-gray-400 my-3"></div>
                <div class="space-y-1 text-red-600">
                    @foreach($order->refunds as $refund)
                        <div class="flex justify-between"><span>Refund {{ $refund->refund_number }}</span><span>- {{ number_format($refund->amount, 2) }}</span></div>
                    @endforeach
                </div>
            @endif

            @if($settings->receipt_footer)
                <div class="border-t border-dashed border-gray-400 my-3"></div>
                <div class="text-center text-xs text-gray-600">{{ $settings->receipt_footer }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
