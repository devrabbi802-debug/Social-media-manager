@extends('layouts.tenant')

@section('title', 'Session #' . $session->id . ' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Session #{{ $session->id }}</h1>
                    <p class="text-gray-600">
                        {{ $session->user->name ?? '-' }} · {{ $session->opened_at?->format('d M Y H:i') }}
                        @if($session->closed_at) — {{ $session->closed_at->format('d M Y H:i') }} @endif
                    </p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('pos.sessions.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition">Back</a>
                    @if($session->status === 'open')
                        <a href="{{ route('pos.index') }}" class="px-4 py-2 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition">Open POS</a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left: summary --}}
        <div class="space-y-6">
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h3 class="font-bold text-gray-900 mb-4">Session Summary</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600"><span>Status</span>
                        <span class="px-2 py-0.5 text-xs rounded-full font-medium {{ $session->status === 'open' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">{{ $session->status }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600"><span>Opening cash</span><span>৳{{ number_format($session->opening_cash, 2) }}</span></div>
                    <div class="flex justify-between text-gray-600"><span>Total sales</span><span class="font-semibold text-gray-900">৳{{ number_format($session->total_sales, 2) }}</span></div>
                    <div class="flex justify-between text-gray-600"><span>Total tax</span><span>৳{{ number_format($session->total_tax, 2) }}</span></div>
                    <div class="flex justify-between text-gray-600"><span>Total discount</span><span class="text-red-500">- ৳{{ number_format($session->total_discount, 2) }}</span></div>
                    <div class="flex justify-between text-gray-600"><span>Sales count</span><span>{{ $session->sales_count }}</span></div>
                    <div class="flex justify-between text-gray-600"><span>Cash sales</span><span>৳{{ number_format($session->cash_sales, 2) }}</span></div>
                    <div class="flex justify-between text-gray-600"><span>Card sales</span><span>৳{{ number_format($session->card_sales, 2) }}</span></div>
                    <div class="flex justify-between text-gray-600"><span>Mobile sales</span><span>৳{{ number_format($session->mobile_sales, 2) }}</span></div>
                    <div class="flex justify-between text-gray-600"><span>Refunds</span><span class="text-red-500">- ৳{{ number_format($session->refunds_total, 2) }}</span></div>
                </div>
            </div>

            @if($session->status === 'open')
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h3 class="font-bold text-gray-900 mb-4">Cash In / Out</h3>
                    <form method="POST" action="{{ route('pos.sessions.cash', $session) }}" class="space-y-3">
                        @csrf
                        <div class="flex gap-2">
                            <select name="type" class="w-28 border border-gray-300 rounded-xl px-3 py-2 text-sm">
                                <option value="in">Cash In</option>
                                <option value="out">Cash Out</option>
                            </select>
                            <input type="number" name="amount" min="0.01" step="0.01" required placeholder="Amount" class="flex-1 border border-gray-300 rounded-xl px-3 py-2 text-sm">
                        </div>
                        <input type="text" name="reason" placeholder="কারণ" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                        <button type="submit" class="w-full px-4 py-2 bg-purple-600 text-white rounded-xl text-sm font-semibold hover:bg-purple-700">Add Entry</button>
                    </form>
                </div>

                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h3 class="font-bold text-gray-900 mb-4">Close Session</h3>
                    <form method="POST" action="{{ route('pos.sessions.close', $session) }}" onsubmit="return confirm('সেশন বন্ধ করবেন?')" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Expected cash: <span class="font-bold text-gray-900">৳{{ number_format($expectedCash, 2) }}</span></label>
                            <input type="number" name="closing_cash" min="0" step="0.01" required placeholder="Declared closing cash" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                        </div>
                        <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-xl text-sm font-semibold hover:bg-red-700">Close Session</button>
                    </form>
                </div>
            @else
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h3 class="font-bold text-gray-900 mb-4">Closing</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between text-gray-600"><span>Closing cash</span><span>৳{{ number_format($session->closing_cash, 2) }}</span></div>
                        <div class="flex justify-between text-gray-600"><span>Expected cash</span><span>৳{{ number_format($session->expected_cash, 2) }}</span></div>
                        <div class="flex justify-between font-bold {{ $session->cash_difference < 0 ? 'text-red-600' : 'text-green-600' }}"><span>Difference</span><span>৳{{ number_format($session->cash_difference, 2) }}</span></div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Right: cash events + orders --}}
        <div class="lg:col-span-2 space-y-6">
            @if($session->cashEvents->count())
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="font-bold text-gray-900">Cash Events</h3>
                    </div>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">কারণ</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">তারিখ</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($session->cashEvents as $event)
                                <tr>
                                    <td class="px-6 py-3 text-sm">
                                        <span class="px-2 py-0.5 text-xs rounded-full font-medium {{ $event->type === 'in' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $event->type }}</span>
                                    </td>
                                    <td class="px-6 py-3 text-sm text-right font-semibold text-gray-900">৳{{ number_format($event->amount, 2) }}</td>
                                    <td class="px-6 py-3 text-sm text-gray-600">{{ $event->reason ?? '-' }}</td>
                                    <td class="px-6 py-3 text-sm text-gray-500">{{ $event->created_at->format('d M H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="font-bold text-gray-900">Orders ({{ $session->orders->count() }})</h3>
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">অর্ডার</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">মোট</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">পেমেন্ট</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">স্ট্যাটাস</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($session->orders as $order)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 text-sm">
                                    <a href="{{ route('pos.sales.show', $order) }}" class="font-semibold text-purple-600 hover:text-purple-800">{{ $order->order_number }}</a>
                                </td>
                                <td class="px-6 py-3 text-sm text-right font-semibold text-gray-900">৳{{ number_format($order->total, 2) }}</td>
                                <td class="px-6 py-3 text-sm text-gray-600">{{ $order->paymentMethodName() }}</td>
                                <td class="px-6 py-3 text-sm">{{ $order->status }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">কোনো অর্ডার নেই</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
