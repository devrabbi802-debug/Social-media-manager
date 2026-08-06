@extends('layouts.tenant')

@section('title', 'POS Reports - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">POS Reports</h1>
                    <p class="text-gray-600">{{ $from }} to {{ $to }}</p>
                </div>
                <div class="flex items-center space-x-3">
                    <form method="GET" class="flex gap-2">
                        <input type="date" name="from" value="{{ $from }}" class="border border-gray-300 rounded-xl px-3 py-2 text-sm">
                        <input type="date" name="to" value="{{ $to }}" class="border border-gray-300 rounded-xl px-3 py-2 text-sm">
                        <button class="px-4 py-2 bg-purple-600 text-white rounded-xl text-sm font-medium">Filter</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Summary --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">Gross Sales</p>
                <p class="text-2xl font-bold text-purple-600">৳{{ number_format($summary['gross_sales'], 2) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">Net Sales</p>
                <p class="text-2xl font-bold text-green-600">৳{{ number_format($summary['net_sales'], 2) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">Profit</p>
                <p class="text-2xl font-bold text-blue-600">৳{{ number_format($summary['profit'], 2) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">Sales Count</p>
                <p class="text-2xl font-bold text-gray-900">{{ $summary['sales_count'] }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">Items Sold</p>
                <p class="text-2xl font-bold text-gray-900">{{ $summary['items_sold'] }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">Tax Collected</p>
                <p class="text-2xl font-bold text-gray-900">৳{{ number_format($summary['tax_collected'], 2) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">Discounts Given</p>
                <p class="text-2xl font-bold text-red-600">- ৳{{ number_format($summary['discounts'], 2) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">Refunds</p>
                <p class="text-2xl font-bold text-red-600">- ৳{{ number_format($summary['refunds'], 2) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Payment breakdown --}}
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200"><h3 class="font-bold text-gray-900">Payment Method Breakdown</h3></div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Count</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($paymentBreakdown as $p)
                            @php
                                $pmAcct = \App\Models\ChartOfAccount::byCode($p->method);
                                $pmName = $pmAcct?->name ?? ucfirst(str_replace('_', ' ', $p->method));
                            @endphp
                            <tr>
                                <td class="px-6 py-3 text-sm text-gray-900">{{ $pmName }}</td>
                                <td class="px-6 py-3 text-sm text-right text-gray-600">{{ $p->count }}</td>
                                <td class="px-6 py-3 text-sm text-right font-semibold text-gray-900">৳{{ number_format($p->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Top products --}}
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200"><h3 class="font-bold text-gray-900">Top Products</h3></div>
                <div class="divide-y divide-gray-200">
                    @foreach($topProducts as $index => $tp)
                        <div class="px-6 py-3 flex items-center gap-3">
                            <span class="w-6 h-6 flex items-center justify-center rounded-full bg-purple-100 text-purple-700 text-xs font-bold">{{ $index + 1 }}</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $tp->name }}</p>
                                <p class="text-xs text-gray-500">{{ $tp->quantity }} pcs sold</p>
                            </div>
                            <span class="text-sm font-semibold text-gray-900">৳{{ number_format($tp->revenue, 2) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Category breakdown --}}
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200"><h3 class="font-bold text-gray-900">Category Breakdown</h3></div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($categoryBreakdown as $cb)
                            <tr>
                                <td class="px-6 py-3 text-sm text-gray-900">{{ $cb->category }}</td>
                                <td class="px-6 py-3 text-sm text-right text-gray-600">{{ $cb->quantity }}</td>
                                <td class="px-6 py-3 text-sm text-right font-semibold text-gray-900">৳{{ number_format($cb->revenue, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Cashiers --}}
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200"><h3 class="font-bold text-gray-900">Sales by Cashier</h3></div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cashier</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Orders</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($cashiers as $c)
                            <tr>
                                <td class="px-6 py-3 text-sm text-gray-900">{{ $c->name }}</td>
                                <td class="px-6 py-3 text-sm text-right text-gray-600">{{ $c->count }}</td>
                                <td class="px-6 py-3 text-sm text-right font-semibold text-gray-900">৳{{ number_format($c->total, 2) }}</td>
                            </tr>
                        @endforeach
                        @if($cashiers->isEmpty())
                            <tr><td colspan="3" class="px-6 py-8 text-center text-gray-500">কোনো ডেটা নেই</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Daily sales --}}
        @if($dailySales->count())
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden mt-6">
                <div class="px-6 py-4 border-b border-gray-200"><h3 class="font-bold text-gray-900">Daily Sales Trend</h3></div>
                <div class="p-6">
                    <div class="flex items-end gap-2 overflow-x-auto pos-scroll" style="height:150px">
                        @php $max = $dailySales->max('sales') ?: 1; @endphp
                        @foreach($dailySales as $day)
                            <div class="flex flex-col items-center gap-1 flex-shrink-0">
                                <span class="text-xs text-gray-500">৳{{ number_format($day->sales) }}</span>
                                <div class="w-10 bg-purple-500 rounded-t" style="height:{{ ($day->sales / $max) * 100 }}px"></div>
                                <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($day->date)->format('d M') }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection