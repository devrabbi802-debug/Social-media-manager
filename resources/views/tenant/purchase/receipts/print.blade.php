<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>Goods Received Note {{ $receipt->receipt_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #1a1a1a; padding: 40px; }
        .header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 40px; padding-bottom: 20px; border-bottom: 2px solid #e5e7eb; }
        .invoice-title h1 { font-size: 26px; color: #7c3aed; margin-bottom: 4px; }
        .invoice-title p { color: #6b7280; font-size: 14px; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 999px; font-size: 11px; font-weight: 600; background: #dcfce7; color: #166534; }
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
    <div class="header">
        <div class="invoice-title">
            <h1>GOODS RECEIVED NOTE</h1>
            <p>#{{ $receipt->receipt_number }}</p>
        </div>
        <div style="text-align: right;">
            @if($business && $business->business_name)
                <h2 style="font-size: 16px; margin-bottom: 4px;">{{ $business->business_name }}</h2>
            @endif
            <span class="badge">Received</span>
            <p style="color: #6b7280; font-size: 12px; margin-top: 6px;">{{ $receipt->receipt_date->format('d M, Y') }}</p>
        </div>
    </div>

    <div class="grid-2">
        <div class="section">
            <h3>Vendor</h3>
            <p>
                <strong>{{ $receipt->supplier->name }}</strong><br>
                @if($receipt->supplier->company){{ $receipt->supplier->company }}<br>@endif
                @if($receipt->supplier->phone){{ $receipt->supplier->phone }}<br>@endif
                @if($receipt->supplier->address){{ $receipt->supplier->address }}<br>@endif
            </p>
        </div>
        <div class="section">
            <h3>Receipt Summary</h3>
            <p>
                <strong>Receipt Date:</strong> {{ $receipt->receipt_date->format('d M, Y') }}<br>
                <strong>Warehouse:</strong> {{ $receipt->warehouse?->name ?? '—' }}<br>
                @if($receipt->purchaseOrder)<strong>Purchase Order:</strong> {{ $receipt->purchaseOrder->po_number }}<br>@endif
                @if($receipt->creator)<strong>Received By:</strong> {{ $receipt->creator->name }}<br>@endif
            </p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Product</th>
                <th class="text-center">Qty</th>
                <th class="text-right">Unit Cost</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($receipt->items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>
                    <span class="font-bold">{{ $item->name }}</span>
                    @if($item->sku)<br><span style="font-size: 11px; color: #9ca3af;">SKU: {{ $item->sku }}</span>@endif
                </td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-right">৳{{ number_format($item->unit_cost, 2) }}</td>
                <td class="text-right">৳{{ number_format($item->line_total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal</td><td class="text-right">৳{{ number_format($receipt->subtotal, 2) }}</td></tr>
        @if($receipt->tax_amount > 0)
            <tr><td>Tax ({{ $receipt->tax_rate }}%)</td><td class="text-right">+৳{{ number_format($receipt->tax_amount, 2) }}</td></tr>
        @endif
        <tr><td class="font-bold">Total</td><td class="text-right font-bold">৳{{ number_format($receipt->total, 2) }}</td></tr>
    </table>

    @if($receipt->notes)
    <div class="section" style="margin-bottom: 20px;">
        <h3>Notes</h3>
        <p>{{ $receipt->notes }}</p>
    </div>
    @endif

    <div class="footer">
        <p>Goods Received Note #{{ $receipt->receipt_number }}</p>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" style="padding: 10px 24px; background: #7c3aed; color: #fff; border: none; border-radius: 8px; font-size: 14px; cursor: pointer;">Print</button>
        <button onclick="window.close()" style="padding: 10px 24px; background: #e5e7eb; color: #374151; border: none; border-radius: 8px; font-size: 14px; cursor: pointer; margin-left: 8px;">Close</button>
    </div>
</body>
</html>
