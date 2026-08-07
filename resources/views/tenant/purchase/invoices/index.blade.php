@extends('layouts.tenant')

@section('title', __('sidebar.purchase_invoices').' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">@lang('sidebar.purchase_invoices')</h1>
                    <p class="text-gray-600">সাপ্লায়ার বিল — ট্র্যাক করুন কাকে কত বাকি</p>
                </div>
                <a href="{{ route('purchase.invoices.create') }}" class="px-4 py-2 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition">+ নতুন বিল</a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @include('tenant.purchase.partials._nav', ['current' => 'invoices'])

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-2xl p-4 mb-6 flex items-center">
            <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <p class="text-green-800 font-medium">{{ session('success') }}</p>
        </div>
        @endif

        @if(session('error'))
        <div class="bg-red-50 border border-red-200 rounded-2xl p-4 mb-6 flex items-center">
            <svg class="w-6 h-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            <p class="text-red-800 font-medium">{{ session('error') }}</p>
        </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">মোট বিল (cancelled ছাড়া)</p>
                <p class="text-2xl font-bold text-gray-900">৳{{ number_format($stats['total'], 2) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">মোট বাকি</p>
                <p class="text-2xl font-bold text-orange-600">৳{{ number_format($stats['due'], 2) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">ওভারডিউ বিল</p>
                <p class="text-2xl font-bold text-red-600">{{ $stats['overdue'] }}</p>
            </div>
        </div>

        <form method="GET" class="bg-white rounded-2xl p-4 shadow-sm mb-6 flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="বিল নম্বর খুঁজুন..." class="flex-1 min-w-[180px] border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 text-sm">
            <select name="status" class="border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 text-sm">
                <option value="">সব স্ট্যাটাস</option>
                @foreach(\App\Models\PurchaseInvoice::STATUSES as $key => $label)
                    <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <select name="supplier_id" class="border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 text-sm">
                <option value="">সব সাপ্লায়ার</option>
                @foreach($suppliers as $s)
                    <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
            <input type="date" name="from" value="{{ request('from') }}" class="border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 text-sm">
            <input type="date" name="to" value="{{ request('to') }}" class="border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 text-sm">
            <button type="submit" class="px-5 py-2 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition text-sm">ফিল্টার</button>
            <a href="{{ route('purchase.invoices.index') }}" class="px-4 py-2 border rounded-xl text-gray-600 hover:bg-gray-50 transition text-sm">রিসেট</a>
        </form>

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
                        <th class="px-6 py-4 font-medium">বিল</th>
                        <th class="px-6 py-4 font-medium">সাপ্লায়ার</th>
                        <th class="px-6 py-4 font-medium">তারিখ</th>
                        <th class="px-6 py-4 font-medium">ডিউ তারিখ</th>
                        <th class="px-6 py-4 font-medium text-right">মোট</th>
                        <th class="px-6 py-4 font-medium text-right">বাকি</th>
                        <th class="px-6 py-4 font-medium">স্ট্যাটাস</th>
                        <th class="px-6 py-4 font-medium text-right">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($invoices as $invoice)
                    <tr class="hover:bg-gray-50 {{ $invoice->isOverdue() ? 'bg-red-50/50' : '' }}">
                        <td class="px-6 py-4">
                            <a href="{{ route('purchase.invoices.show', $invoice) }}" class="font-medium text-purple-600 hover:underline">{{ $invoice->invoice_number }}</a>
                            @if($invoice->isOverdue())
                                <span class="inline-flex items-center px-2 py-0.5 ml-1 rounded-full text-xs font-medium bg-red-100 text-red-700">Overdue</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $invoice->supplier->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $invoice->invoice_date->format('d M Y') }}</td>
                        <td class="px-6 py-4 text-sm {{ $invoice->isOverdue() ? 'text-red-600 font-medium' : 'text-gray-500' }}">{{ $invoice->due_date?->format('d M Y') ?? '—' }}</td>
                        <td class="px-6 py-4 text-right font-medium">৳{{ number_format($invoice->total, 2) }}</td>
                        <td class="px-6 py-4 text-right font-semibold {{ $invoice->due() > 0 ? 'text-orange-600' : 'text-green-600' }}">৳{{ number_format($invoice->due(), 2) }}</td>
                        <td class="px-6 py-4">@include('tenant.purchase.partials._invoice-status', ['status' => $invoice->status])</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if(in_array($invoice->status, ['awaiting_payment', 'partially_paid']))
                                    <a href="{{ route('purchase.payments.create', ['invoice_id' => $invoice->id]) }}" class="text-green-600 hover:text-green-800 text-xs font-medium">Pay</a>
                                @endif
                                <a href="{{ route('purchase.invoices.show', $invoice) }}" class="text-purple-600 hover:text-purple-800 text-xs font-medium">View</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-6 py-12 text-center text-gray-500">কোনো বিল নেই</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($invoices->hasPages())<div class="px-6 py-4 border-t">{{ $invoices->withQueryString()->links() }}</div>@endif
        </div>
    </div>
</div>
@endsection
