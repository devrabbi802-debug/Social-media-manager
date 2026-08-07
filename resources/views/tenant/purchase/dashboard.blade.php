@extends('layouts.tenant')

@section('title', __('sidebar.purchase_dashboard').' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">@lang('sidebar.purchase_dashboard')</h1>
                    <p class="text-gray-600">সাপ্লায়ার, অর্ডার, মাল রিসিভ ও বিল — সব এক জায়গায়</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('purchase.orders.create') }}" class="px-4 py-2 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition">+ নতুন অর্ডার</a>
                    <a href="{{ route('purchase.receipts.create') }}" class="px-4 py-2 bg-white text-purple-600 border border-purple-300 rounded-xl font-medium hover:bg-purple-50 transition">+ মাল রিসিভ</a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @include('tenant.purchase.partials._nav', ['current' => 'dashboard'])

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-2xl p-4 mb-6 flex items-center">
            <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <p class="text-green-800 font-medium">{{ session('success') }}</p>
        </div>
        @endif

        {{-- KPI Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-2xl p-5 shadow-sm border-l-4 border-purple-500">
                <p class="text-xs text-gray-500 font-medium">সক্রিয় সাপ্লায়ার</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($stats['suppliers']) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border-l-4 border-blue-500">
                <p class="text-xs text-gray-500 font-medium">খোলা পারচেজ অর্ডার</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($stats['orders']) }}</p>
                <p class="text-xs text-blue-600 mt-1">{{ $stats['pending_receipts'] }} ইউনিট রিসিভ বাকি</p>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border-l-4 border-green-500">
                <p class="text-xs text-gray-500 font-medium">মোট বকেয়া (আমাদের দেনা)</p>
                <p class="text-3xl font-bold text-green-600 mt-1">৳{{ number_format($stats['outstanding'], 2) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border-l-4 border-orange-500">
                <p class="text-xs text-gray-500 font-medium">ওভারডিউ বিল</p>
                <p class="text-3xl font-bold text-orange-600 mt-1">{{ number_format($overdueInvoices->count()) }}</p>
                <a href="{{ route('purchase.invoices.index') }}" class="text-xs text-orange-600 hover:underline">বিস্তারিত দেখুন →</a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Recent Orders --}}
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-900">সাম্প্রতিক পারচেজ অর্ডার</h2>
                    <a href="{{ route('purchase.orders.index') }}" class="text-sm text-purple-600 hover:underline">সব দেখুন</a>
                </div>
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
                            <th class="px-6 py-3 font-medium">অর্ডার</th>
                            <th class="px-6 py-3 font-medium">সাপ্লায়ার</th>
                            <th class="px-6 py-3 font-medium">মোট</th>
                            <th class="px-6 py-3 font-medium">স্ট্যাটাস</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($recentOrders as $order)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3">
                                <a href="{{ route('purchase.orders.show', $order) }}" class="font-medium text-purple-600 hover:underline">{{ $order->po_number }}</a>
                                <p class="text-xs text-gray-500">{{ $order->order_date->format('d M Y') }}</p>
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-700">{{ $order->supplier->name }}</td>
                            <td class="px-6 py-3 font-semibold">৳{{ number_format($order->total, 2) }}</td>
                            <td class="px-6 py-3">
                                @include('tenant.purchase.partials._order-status', ['status' => $order->status])
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-6 py-10 text-center text-gray-500">কোনো অর্ডার নেই</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Overdue Bills --}}
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-900">ওভারডিউ বিল</h2>
                    <a href="{{ route('purchase.invoices.index') }}" class="text-sm text-purple-600 hover:underline">সব দেখুন</a>
                </div>
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
                            <th class="px-6 py-3 font-medium">বিল</th>
                            <th class="px-6 py-3 font-medium">সাপ্লায়ার</th>
                            <th class="px-6 py-3 font-medium">ডিউ</th>
                            <th class="px-6 py-3 font-medium">বাকি</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($overdueInvoices as $invoice)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3">
                                <a href="{{ route('purchase.invoices.show', $invoice) }}" class="font-medium text-purple-600 hover:underline">{{ $invoice->invoice_number }}</a>
                                <p class="text-xs text-red-500">ডিউ: {{ $invoice->due_date?->format('d M Y') }}</p>
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-700">{{ $invoice->supplier->name }}</td>
                            <td class="px-6 py-3 text-sm">৳{{ number_format($invoice->total, 2) }}</td>
                            <td class="px-6 py-3 font-semibold text-red-600">৳{{ number_format($invoice->due(), 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-6 py-10 text-center text-gray-500">কোনো ওভারডিউ বিল নেই</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Recent Receipts --}}
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-900">সাম্প্রতিক মাল রিসিভ (GRN)</h2>
                    <a href="{{ route('purchase.receipts.index') }}" class="text-sm text-purple-600 hover:underline">সব দেখুন</a>
                </div>
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
                            <th class="px-6 py-3 font-medium">রিসিপ্ট</th>
                            <th class="px-6 py-3 font-medium">সাপ্লায়ার</th>
                            <th class="px-6 py-3 font-medium">তারিখ</th>
                            <th class="px-6 py-3 font-medium">মোট</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($recentReceipts as $receipt)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3">
                                <a href="{{ route('purchase.receipts.show', $receipt) }}" class="font-medium text-purple-600 hover:underline">{{ $receipt->receipt_number }}</a>
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-700">{{ $receipt->supplier->name }}</td>
                            <td class="px-6 py-3 text-sm text-gray-500">{{ $receipt->receipt_date->format('d M Y') }}</td>
                            <td class="px-6 py-3 font-semibold">৳{{ number_format($receipt->total, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-6 py-10 text-center text-gray-500">কোনো রিসিপ্ট নেই</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Recent Invoices --}}
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-900">সাম্প্রতিক বিল</h2>
                    <a href="{{ route('purchase.invoices.index') }}" class="text-sm text-purple-600 hover:underline">সব দেখুন</a>
                </div>
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
                            <th class="px-6 py-3 font-medium">বিল</th>
                            <th class="px-6 py-3 font-medium">সাপ্লায়ার</th>
                            <th class="px-6 py-3 font-medium">মোট</th>
                            <th class="px-6 py-3 font-medium">স্ট্যাটাস</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($recentInvoices as $invoice)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3">
                                <a href="{{ route('purchase.invoices.show', $invoice) }}" class="font-medium text-purple-600 hover:underline">{{ $invoice->invoice_number }}</a>
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-700">{{ $invoice->supplier->name }}</td>
                            <td class="px-6 py-3 font-semibold">৳{{ number_format($invoice->total, 2) }}</td>
                            <td class="px-6 py-3">
                                @include('tenant.purchase.partials._invoice-status', ['status' => $invoice->status])
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-6 py-10 text-center text-gray-500">কোনো বিল নেই</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
