@extends('layouts.tenant')

@section('title', __('sidebar.sales_reports').' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">@lang('sidebar.sales_reports')</h1>
                    <p class="text-gray-600">স্টোরফ্রন্ট অর্ডার ও সেলস বিশ্লেষণ</p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @include('tenant.reports.partials._nav', ['current' => 'sales'])

        <form method="GET" class="bg-white rounded-2xl p-4 shadow-sm mb-6 flex flex-wrap gap-3">
            <div class="flex items-center gap-2">
                <label class="text-sm text-gray-600">থেকে</label>
                <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="border border-gray-300 rounded-xl px-4 py-2 text-sm">
            </div>
            <div class="flex items-center gap-2">
                <label class="text-sm text-gray-600">পর্যন্ত</label>
                <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="border border-gray-300 rounded-xl px-4 py-2 text-sm">
            </div>
            <select name="status" class="border border-gray-300 rounded-xl px-4 py-2 text-sm">
                <option value="">সব স্ট্যাটাস</option>
                @foreach(['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'] as $s)
                    <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>@lang("orders.{$s}")</option>
                @endforeach
            </select>
            <select name="payment_status" class="border border-gray-300 rounded-xl px-4 py-2 text-sm">
                <option value="">সব পেমেন্ট স্ট্যাটাস</option>
                @foreach(['pending', 'paid', 'failed', 'refunded'] as $ps)
                    <option value="{{ $ps }}" {{ $paymentStatus === $ps ? 'selected' : '' }}>@lang("orders.{$ps}")</option>
                @endforeach
            </select>
            <button type="submit" class="px-5 py-2 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition text-sm">রিপোর্ট দেখুন</button>
            @if($status || $paymentStatus || request()->filled('from') || request()->filled('to'))
                <a href="{{ route('reports.sales') }}" class="px-5 py-2 border border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition text-sm">রিসেট</a>
            @endif
        </form>

        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">মোট রেভিনিউ</p>
                <p class="text-xl font-bold text-purple-600">৳{{ number_format($summary['revenue'], 2) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">অর্ডার</p>
                <p class="text-xl font-bold text-gray-900">{{ $summary['orders_count'] }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">ভ্যালিড অর্ডার</p>
                <p class="text-xl font-bold text-gray-900">{{ $summary['valid_orders_count'] }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">গড় অর্ডার ভ্যালু</p>
                <p class="text-xl font-bold text-gray-900">৳{{ number_format($summary['avg_order_value'], 2) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">বিক্রিত আইটেম</p>
                <p class="text-xl font-bold text-gray-900">{{ $summary['items_sold'] }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">ডিসকাউন্ট</p>
                <p class="text-xl font-bold text-red-600">-৳{{ number_format($summary['discount'], 2) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">শিপিং</p>
                <p class="text-xl font-bold text-gray-900">৳{{ number_format($summary['shipping'], 2) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">বাকি</p>
                <p class="text-xl font-bold text-orange-600">৳{{ number_format($summary['due'], 2) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200"><h3 class="font-bold text-gray-900">স্ট্যাটাস অনুযায়ী</h3></div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">স্ট্যাটাস</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">অর্ডার</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">মোট</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($byStatus as $key => $row)
                            <tr>
                                <td class="px-6 py-3 text-sm text-gray-900">@lang("orders.{$key}")</td>
                                <td class="px-6 py-3 text-sm text-right text-gray-600">{{ $row['count'] }}</td>
                                <td class="px-6 py-3 text-sm text-right font-semibold text-gray-900">৳{{ number_format($row['total'], 2) }}</td>
                            </tr>
                        @endforeach
                        @if($byStatus->isEmpty())
                            <tr><td colspan="3" class="px-6 py-8 text-center text-gray-500">কোনো ডেটা নেই</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200"><h3 class="font-bold text-gray-900">পেমেন্ট মেথড</h3></div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">মেথড</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">অর্ডার</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">মোট</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($byPaymentMethod as $key => $row)
                            <tr>
                                <td class="px-6 py-3 text-sm text-gray-900">{{ $key ?: 'N/A' }}</td>
                                <td class="px-6 py-3 text-sm text-right text-gray-600">{{ $row['count'] }}</td>
                                <td class="px-6 py-3 text-sm text-right font-semibold text-gray-900">৳{{ number_format($row['total'], 2) }}</td>
                            </tr>
                        @endforeach
                        @if($byPaymentMethod->isEmpty())
                            <tr><td colspan="3" class="px-6 py-8 text-center text-gray-500">কোনো ডেটা নেই</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200"><h3 class="font-bold text-gray-900">পেমেন্ট স্ট্যাটাস</h3></div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">স্ট্যাটাস</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">অর্ডার</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">মোট</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($byPaymentStatus as $key => $row)
                            <tr>
                                <td class="px-6 py-3 text-sm text-gray-900">@lang("orders.{$key}")</td>
                                <td class="px-6 py-3 text-sm text-right text-gray-600">{{ $row['count'] }}</td>
                                <td class="px-6 py-3 text-sm text-right font-semibold text-gray-900">৳{{ number_format($row['total'], 2) }}</td>
                            </tr>
                        @endforeach
                        @if($byPaymentStatus->isEmpty())
                            <tr><td colspan="3" class="px-6 py-8 text-center text-gray-500">কোনো ডেটা নেই</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            @if($dailySales->count())
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200"><h3 class="font-bold text-gray-900">ডেইলি সেলস ট্রেন্ড</h3></div>
                    <div class="p-6">
                        <div class="flex items-end gap-2 overflow-x-auto" style="height:150px">
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

            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200"><h3 class="font-bold text-gray-900">সেরা বিক্রিত পণ্য</h3></div>
                <div class="divide-y divide-gray-200">
                    @foreach($topProducts as $index => $tp)
                        <div class="px-6 py-3 flex items-center gap-3">
                            <span class="w-6 h-6 flex items-center justify-center rounded-full bg-purple-100 text-purple-700 text-xs font-bold">{{ $index + 1 }}</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $tp->name }}</p>
                                <p class="text-xs text-gray-500">{{ $tp->quantity }} pcs sold{{ $tp->sku ? ' · '.$tp->sku : '' }}</p>
                            </div>
                            <span class="text-sm font-semibold text-gray-900">৳{{ number_format($tp->revenue, 2) }}</span>
                        </div>
                    @endforeach
                    @if($topProducts->isEmpty())
                        <div class="px-6 py-8 text-center text-gray-500">কোনো ডেটা নেই</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="font-bold text-gray-900">অর্ডার তালিকা</h3>
                <span class="text-sm text-gray-500">{{ $orders->total() }} টি অর্ডার</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">অর্ডার #</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">কাস্টমার</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">তারিখ</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">স্ট্যাটাস</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">পেমেন্ট</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">মোট</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($orders as $order)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium text-purple-600">
                                    <a href="{{ route('orders.show', $order) }}">{{ $order->order_number }}</a>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $order->customer_name ?: 'Guest' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $order->created_at->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-sm">
                                    @php
                                        $statusColors = ['pending' => 'bg-yellow-100 text-yellow-800', 'processing' => 'bg-blue-100 text-blue-800', 'shipped' => 'bg-indigo-100 text-indigo-800', 'delivered' => 'bg-green-100 text-green-800', 'cancelled' => 'bg-red-100 text-red-800', 'refunded' => 'bg-gray-100 text-gray-800'];
                                    @endphp
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-800' }}">@lang("orders.{$order->status}")</span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $order->payment_method ?: 'N/A' }} · @lang("orders.{$order->payment_status}")</td>
                                <td class="px-4 py-3 text-sm font-semibold text-right text-gray-900">৳{{ number_format($order->total, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-10 text-center text-gray-500">কোনো অর্ডার নেই</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($orders->hasPages())
                <div class="px-4 py-3 border-t border-gray-200">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
