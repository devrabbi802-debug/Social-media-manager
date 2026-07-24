<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('orders.print_title', ['order' => $order->order_number]) }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #1a1a1a; padding: 40px; }
        .header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 40px; padding-bottom: 20px; border-bottom: 2px solid #e5e7eb; }
        .invoice-title h1 { font-size: 28px; color: #7c3aed; margin-bottom: 4px; }
        .invoice-title p { color: #6b7280; font-size: 14px; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 999px; font-size: 11px; font-weight: 600; }
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-yellow { background: #fef3c7; color: #92400e; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-blue { background: #dbeafe; color: #1e40af; }
        .badge-gray { background: #f3f4f6; color: #374151; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 40px; }
        .section h3 { font-size: 14px; font-weight: 600; margin-bottom: 8px; color: #374151; }
        .section p { color: #6b7280; font-size: 12px; line-height: 1.6; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 40px; }
        th { background: #f9fafb; text-align: left; padding: 10px 12px; font-size: 11px; text-transform: uppercase; color: #6b7280; border-bottom: 1px solid #e5e7eb; }
        td { padding: 10px 12px; border-bottom: 1px solid #f3f4f6; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: 700; }
        .totals { margin-left: auto; width: 300px; }
        .totals tr:last-child td { border-top: 2px solid #374151; padding-top: 12px; font-size: 16px; }
        .footer { text-align: center; color: #9ca3af; font-size: 11px; border-top: 1px solid #e5e7eb; padding-top: 20px; margin-top: 40px; }
        @media print { body { padding: 20px; } .no-print { display: none; } }
    </style>
</head>
<body>
    @php
        $logoUrl = null;
        if (isset($storefront) && $storefront && $storefront->store_logo) {
            $logoUrl = asset('storage/' . $storefront->store_logo);
        }
    @endphp
    <div class="header">
        <div style="display: flex; align-items: center; gap: 16px;">
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="Logo" style="height: 50px; width: auto; object-fit: contain;">
            @endif
            <div class="invoice-title">
                <h1>@lang('orders.invoice')</h1>
                <p>#{{ $order->order_number }}</p>
            </div>
        </div>
        <div>
            @php
                $bClass = match($order->status) { 'delivered' => 'badge-green', 'processing' => 'badge-blue', 'shipped' => 'badge-blue', 'cancelled' => 'badge-red', 'refunded' => 'badge-gray', default => 'badge-yellow' };
            @endphp
            <span class="badge {{ $bClass }}">{{ __("orders.{$order->status}") }}</span>
        </div>
    </div>

    @php
        $qrData = url('/supermaster/orders/' . $order->id);
    @endphp

    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 30px;">
        <div class="section">
            <h3>@lang('orders.customer_info')</h3>
            <p>
                <strong>{{ $order->customer_name }}</strong><br>
                {{ $order->customer_phone }}<br>
                {{ $order->customer->email ?? '' }}
            </p>
        </div>
        <div class="section">
            <h3>@lang('orders.shipping_info')</h3>
            <p>
                @if($order->shippingAddress)
                    {{ $order->shippingAddress->address }}, {{ $order->shippingAddress->city }}, {{ $order->shippingAddress->district }}{{ $order->shippingAddress->zip ? ' - '.$order->shippingAddress->zip : '' }}<br>
                @endif
                @if($order->carrier)<strong>@lang('orders.carrier'):</strong> {{ $order->carrier }}<br>@endif
                @if($order->tracking_id)<strong>@lang('orders.tracking_id'):</strong> {{ $order->tracking_id }}@endif
            </p>
        </div>
        <div class="section" style="text-align: right;">
            <h3>@lang('orders.order_summary')</h3>
            <p>
                <strong>@lang('orders.date'):</strong> {{ $order->created_at->format('d M, Y h:i A') }}<br>
                <strong>@lang('orders.payment_method'):</strong> {{ $order->payment_method ?? '-' }}<br>
                <strong>@lang('orders.payment_status'):</strong> {{ __("orders.{$order->payment_status}") }}
            </p>
            <div style="margin-top: 10px;">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data={{ urlencode($qrData) }}"
                     alt="QR" style="image-rendering: pixelated; display: inline-block;">
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>@lang('orders.product')</th>
                <th class="text-center">@lang('orders.qty')</th>
                <th class="text-right">@lang('orders.unit_price')</th>
                <th class="text-right">@lang('orders.total_price')</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>
                    <span class="font-bold">{{ $item->name }}</span>
                    @if($item->sku)<br><span style="font-size: 11px; color: #9ca3af;">SKU: {{ $item->sku }}</span>@endif
                </td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-right">৳{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right">৳{{ number_format($item->total_price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>@lang('orders.subtotal')</td><td class="text-right">৳{{ number_format($order->subtotal, 2) }}</td></tr>
        @if($order->shipping_cost > 0)<tr><td>@lang('orders.shipping_cost')</td><td class="text-right">৳{{ number_format($order->shipping_cost, 2) }}</td></tr>@endif
        @if($order->tax > 0)<tr><td>@lang('orders.tax')</td><td class="text-right">৳{{ number_format($order->tax, 2) }}</td></tr>@endif
        @if($order->discount > 0)<tr><td>@lang('orders.discount')</td><td class="text-right">-৳{{ number_format($order->discount, 2) }}</td></tr>@endif
        <tr><td class="font-bold">@lang('orders.total')</td><td class="text-right font-bold">৳{{ number_format($order->total, 2) }}</td></tr>
    </table>

    @if($order->notes)
    <div class="section" style="margin-bottom: 20px;">
        <h3>@lang('orders.notes')</h3>
        <p>{{ $order->notes }}</p>
    </div>
    @endif

    <div class="footer">
        <p>@lang('orders.print_title', ['order' => $order->order_number])</p>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" style="padding: 10px 24px; background: #7c3aed; color: #fff; border: none; border-radius: 8px; font-size: 14px; cursor: pointer;">@lang('orders.print')</button>
        <button onclick="window.close()" style="padding: 10px 24px; background: #e5e7eb; color: #374151; border: none; border-radius: 8px; font-size: 14px; cursor: pointer; margin-left: 8px;">Close</button>
    </div>
</body>
</html>
