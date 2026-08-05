@extends('layouts.tenant')

@section('title', 'POS Sale - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $order->order_number }}</h1>
                    <p class="text-gray-600">{{ $order->created_at->format('d M Y H:i') }} · {{ $order->user->name ?? '-' }}</p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('pos.sales.receipt', $order) }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition">🖨️ Receipt</a>
                    <a href="{{ route('pos.sales.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition">Back</a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            {{-- Items --}}
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="font-bold text-gray-900">Items ({{ $order->items->sum('quantity') }})</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">পণ্য</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">দাম</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">পরিমাণ</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">মোট</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($order->items as $item)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $item->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $item->sku ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-right text-gray-700">৳{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="px-6 py-4 text-sm text-right text-gray-700">{{ $item->quantity }}</td>
                                    <td class="px-6 py-4 text-sm text-right font-semibold text-gray-900">৳{{ number_format($item->total_price, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Refunds --}}
            @if($order->refunds->count())
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="font-bold text-gray-900">Refunds</h3>
                    </div>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">রিফান্ড নম্বর</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">মেথড</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">পরিমাণ</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">কারণ</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">তারিখ</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($order->refunds as $refund)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $refund->refund_number }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $refund->method }}</td>
                                    <td class="px-6 py-4 text-sm text-right text-red-600 font-semibold">৳{{ number_format($refund->amount, 2) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $refund->reason ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $refund->created_at->format('d M Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Right column: summary + actions --}}
        <div class="space-y-6">
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h3 class="font-bold text-gray-900 mb-4">Summary</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600"><span>Subtotal</span><span>৳{{ number_format($order->subtotal, 2) }}</span></div>
                    <div class="flex justify-between text-gray-600"><span>Discount</span><span class="text-red-500">- ৳{{ number_format($order->discount_amount, 2) }}</span></div>
                    <div class="flex justify-between text-gray-600"><span>Tax</span><span>৳{{ number_format($order->tax_amount, 2) }}</span></div>
                    <div class="flex justify-between text-lg font-bold text-gray-900 border-t border-gray-200 pt-2"><span>Total</span><span>৳{{ number_format($order->total, 2) }}</span></div>
                    @if($order->refundedTotal() > 0)
                        <div class="flex justify-between text-red-600"><span>Refunded</span><span>- ৳{{ number_format($order->refundedTotal(), 2) }}</span></div>
                        <div class="flex justify-between font-bold text-green-700"><span>Net</span><span>৳{{ number_format($order->total - $order->refundedTotal(), 2) }}</span></div>
                    @endif
                </div>
                <div class="mt-4 pt-4 border-t border-gray-200 space-y-2 text-sm text-gray-600">
                    <div class="flex justify-between"><span>Customer</span><span>{{ $order->customer_name ?? '-' }} {{ $order->customer_phone ? '('.$order->customer_phone.')' : '' }}</span></div>
                    <div class="flex justify-between"><span>Payment</span><span>{{ $order->payment_method }} ({{ $order->payment_status }})</span></div>
                    <div class="flex justify-between"><span>Tendered</span><span>৳{{ number_format($order->tendered_amount, 2) }}</span></div>
                    <div class="flex justify-between"><span>Change</span><span>৳{{ number_format($order->change_due, 2) }}</span></div>
                    @if($order->session)
                        <div class="flex justify-between"><span>Session</span><span>#{{ $order->session->id }}</span></div>
                    @endif
                </div>
            </div>

            @if($order->status === 'completed')
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h3 class="font-bold text-gray-900 mb-4">Refund</h3>
                    @php $refundable = max($order->total - $order->refundedTotal(), 0); @endphp
                    @if($refundable > 0)
                        <form method="POST" action="{{ route('pos.sales.refund', $order) }}" onsubmit="return confirm('রিফান্ড নিশ্চিত করছেন?')">
                            @csrf
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-1">পরিমাণ (সর্বোচ্চ ৳{{ number_format($refundable, 2) }})</label>
                                    <input type="number" name="amount" min="0.01" max="{{ $refundable }}" step="0.01" required class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-1">মেথড</label>
                                    <select name="method" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                                        <option value="cash">Cash</option>
                                        <option value="card">Card</option>
                                        <option value="mobile">Mobile</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-1">কারণ</label>
                                    <textarea name="reason" rows="2" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm"></textarea>
                                </div>
                                <button type="submit" class="w-full px-4 py-2.5 bg-red-600 text-white rounded-xl text-sm font-semibold hover:bg-red-700">প্রসেস রিফান্ড</button>
                            </div>
                        </form>
                    @else
                        <p class="text-sm text-gray-500">এই অর্ডার সম্পূর্ণ রিফান্ড হয়ে গেছে।</p>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
