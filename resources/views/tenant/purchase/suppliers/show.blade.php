@extends('layouts.tenant')

@section('title', $supplier->name.' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $supplier->name }}</h1>
                    <p class="text-gray-600">
                        {{ $supplier->company ?? '' }}
                        @if($supplier->phone) • {{ $supplier->phone }} @endif
                        @if($supplier->email) • {{ $supplier->email }} @endif
                    </p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('purchase.payments.create', ['supplier_id' => $supplier->id]) }}" class="px-4 py-2 bg-green-600 text-white rounded-xl font-medium hover:bg-green-700 transition">+ পেমেন্ট দিন</a>
                    <a href="{{ route('purchase.suppliers.edit', $supplier) }}" class="px-4 py-2 bg-white text-purple-600 border border-purple-300 rounded-xl font-medium hover:bg-purple-50 transition">Edit</a>
                    <a href="{{ route('purchase.suppliers.index') }}" class="px-4 py-2 border rounded-xl text-gray-600 hover:bg-gray-50 transition">← @lang('common.back')</a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-2xl p-4 mb-6 flex items-center">
            <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <p class="text-green-800 font-medium">{{ session('success') }}</p>
        </div>
        @endif

        {{-- Balance cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-5 shadow-sm">
                <p class="text-xs text-gray-500">মোট ক্রয়</p>
                <p class="text-2xl font-bold text-gray-900">৳{{ number_format($supplier->totalPurchases(), 2) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm">
                <p class="text-xs text-gray-500">মোট পেমেন্ট</p>
                <p class="text-2xl font-bold text-green-600">৳{{ number_format($supplier->totalPaid(), 2) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm">
                <p class="text-xs text-gray-500">রিটার্ন</p>
                <p class="text-2xl font-bold text-blue-600">৳{{ number_format($supplier->totalReturns(), 2) }}</p>
            </div>
            <div class="bg-white rounded-2xl p-5 shadow-sm border-l-4 {{ $supplier->balance() > 0 ? 'border-orange-500' : 'border-green-500' }}">
                <p class="text-xs text-gray-500">বকেয়া (আমাদের দেনা)</p>
                <p class="text-2xl font-bold {{ $supplier->balance() > 0 ? 'text-orange-600' : 'text-green-600' }}">৳{{ number_format($supplier->balance(), 2) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Invoices --}}
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b">
                    <h2 class="text-lg font-bold text-gray-900">বিল সমূহ</h2>
                </div>
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
                            <th class="px-6 py-3 font-medium">বিল</th>
                            <th class="px-6 py-3 font-medium">তারিখ</th>
                            <th class="px-6 py-3 font-medium text-right">মোট</th>
                            <th class="px-6 py-3 font-medium text-right">বাকি</th>
                            <th class="px-6 py-3 font-medium">স্ট্যাটাস</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($invoices as $invoice)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3">
                                <a href="{{ route('purchase.invoices.show', $invoice) }}" class="font-medium text-purple-600 hover:underline">{{ $invoice->invoice_number }}</a>
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-500">{{ $invoice->invoice_date->format('d M Y') }}</td>
                            <td class="px-6 py-3 text-right font-medium">৳{{ number_format($invoice->total, 2) }}</td>
                            <td class="px-6 py-3 text-right font-semibold {{ $invoice->due() > 0 ? 'text-orange-600' : 'text-green-600' }}">৳{{ number_format($invoice->due(), 2) }}</td>
                            <td class="px-6 py-3">@include('tenant.purchase.partials._invoice-status', ['status' => $invoice->status])</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-6 py-10 text-center text-gray-500">কোনো বিল নেই</td></tr>
                        @endforelse
                    </tbody>
                </table>
                @if($invoices->hasPages())<div class="px-6 py-4 border-t">{{ $invoices->links() }}</div>@endif
            </div>

            {{-- Payments --}}
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b">
                    <h2 class="text-lg font-bold text-gray-900">পেমেন্ট ইতিহাস</h2>
                </div>
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
                            <th class="px-6 py-3 font-medium">পেমেন্ট</th>
                            <th class="px-6 py-3 font-medium">তারিখ</th>
                            <th class="px-6 py-3 font-medium">মাধ্যম</th>
                            <th class="px-6 py-3 font-medium text-right">পরিমাণ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($payments as $payment)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 font-medium text-gray-900">{{ $payment->payment_number }}</td>
                            <td class="px-6 py-3 text-sm text-gray-500">{{ $payment->payment_date->format('d M Y') }}</td>
                            <td class="px-6 py-3 text-sm text-gray-600">{{ $payment->methodName() }}</td>
                            <td class="px-6 py-3 text-right font-semibold text-green-600">৳{{ number_format($payment->amount, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-6 py-10 text-center text-gray-500">কোনো পেমেন্ট নেই</td></tr>
                        @endforelse
                    </tbody>
                </table>
                @if($payments->hasPages())<div class="px-6 py-4 border-t">{{ $payments->links() }}</div>@endif
            </div>
        </div>
    </div>
</div>
@endsection
