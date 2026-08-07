@extends('layouts.tenant')

@section('title', __('sidebar.purchase_reports').' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">@lang('sidebar.purchase_reports')</h1>
                    <p class="text-gray-600">ক্রয়, পেমেন্ট, বাকি এবং AP এজিং বিশ্লেষণ</p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @include('tenant.purchase.partials._nav', ['current' => 'reports'])

        <form method="GET" class="bg-white rounded-2xl p-4 shadow-sm mb-6 flex flex-wrap gap-3">
            <div class="flex items-center gap-2">
                <label class="text-sm text-gray-600">থেকে</label>
                <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 text-sm">
            </div>
            <div class="flex items-center gap-2">
                <label class="text-sm text-gray-600">পর্যন্ত</label>
                <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 text-sm">
            </div>
            <select name="supplier_id" class="border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 text-sm">
                <option value="">সব সাপ্লায়ার</option>
                @foreach($suppliers as $s)
                    <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-5 py-2 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition text-sm">রিপোর্ট দেখুন</button>
        </form>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">মোট ক্রয়</p>
                <p class="text-xl font-bold text-gray-900">৳{{ number_format($summary['total_purchase'], 2) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">মোট পেমেন্ট</p>
                <p class="text-xl font-bold text-green-600">৳{{ number_format($summary['total_paid'], 2) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">মোট বাকি</p>
                <p class="text-xl font-bold text-orange-600">৳{{ number_format($summary['total_due'], 2) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">বিল সংখ্যা</p>
                <p class="text-xl font-bold text-gray-900">{{ $summary['invoice_count'] }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">মোট ট্যাক্স</p>
                <p class="text-xl font-bold text-gray-900">৳{{ number_format($summary['tax'], 2) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">মোট ডিসকাউন্ট</p>
                <p class="text-xl font-bold text-gray-900">৳{{ number_format($summary['discount'], 2) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b">
                    <h2 class="text-lg font-bold text-gray-900">সাপ্লায়ার ভিত্তিক ক্রয়</h2>
                </div>
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
                            <th class="px-6 py-3 font-medium">সাপ্লায়ার</th>
                            <th class="px-6 py-3 font-medium text-center">বিল</th>
                            <th class="px-6 py-3 font-medium text-right">ক্রয়</th>
                            <th class="px-6 py-3 font-medium text-right">পেমেন্ট</th>
                            <th class="px-6 py-3 font-medium text-right">বাকি</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($bySupplier as $row)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3">
                                <a href="{{ route('purchase.suppliers.show', $row['supplier']) }}" class="font-medium text-purple-600 hover:underline">{{ $row['supplier']->name }}</a>
                            </td>
                            <td class="px-6 py-3 text-center text-sm">{{ $row['count'] }}</td>
                            <td class="px-6 py-3 text-right font-medium">৳{{ number_format($row['total'], 2) }}</td>
                            <td class="px-6 py-3 text-right text-green-600">৳{{ number_format($row['paid'], 2) }}</td>
                            <td class="px-6 py-3 text-right font-semibold {{ $row['due'] > 0 ? 'text-orange-600' : 'text-green-600' }}">৳{{ number_format($row['due'], 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-6 py-10 text-center text-gray-500">কোনো তথ্য নেই</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b">
                    <h2 class="text-lg font-bold text-gray-900">AP এজিং (বাকি টাকা)</h2>
                </div>
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
                            <th class="px-6 py-3 font-medium">বাকেট</th>
                            <th class="px-6 py-3 font-medium text-center">বিল</th>
                            <th class="px-6 py-3 font-medium text-right">বাকি</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($aging as $bucket)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 text-sm text-gray-700">{{ $bucket['label'] }}</td>
                            <td class="px-6 py-3 text-center text-sm">{{ $bucket['count'] }}</td>
                            <td class="px-6 py-3 text-right font-semibold {{ $bucket['total'] > 0 ? 'text-orange-600' : 'text-gray-400' }}">৳{{ number_format($bucket['total'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-bold text-gray-900">পণ্য ভিত্তিক ক্রয়</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
                            <th class="px-6 py-3 font-medium">পণ্য</th>
                            <th class="px-6 py-3 font-medium">SKU</th>
                            <th class="px-6 py-3 font-medium text-center">পরিমাণ</th>
                            <th class="px-6 py-3 font-medium text-right">মূল্য</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($byProduct as $row)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 font-medium text-gray-900">{{ $row['name'] }}</td>
                            <td class="px-6 py-3 text-sm text-gray-500">{{ $row['sku'] ?? '—' }}</td>
                            <td class="px-6 py-3 text-center text-sm">{{ $row['quantity'] }}</td>
                            <td class="px-6 py-3 text-right font-semibold">৳{{ number_format($row['cost'], 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-6 py-10 text-center text-gray-500">কোনো তথ্য নেই</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
