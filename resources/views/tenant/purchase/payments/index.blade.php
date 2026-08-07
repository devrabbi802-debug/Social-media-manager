@extends('layouts.tenant')

@section('title', __('sidebar.supplier_payments').' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">@lang('sidebar.supplier_payments')</h1>
                    <p class="text-gray-600">সাপ্লায়ারকে করা পেমেন্টসমূহ</p>
                </div>
                <a href="{{ route('purchase.payments.create') }}" class="px-4 py-2 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition">+ নতুন পেমেন্ট</a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @include('tenant.purchase.partials._nav', ['current' => 'payments'])

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

        <div class="bg-white rounded-2xl p-4 shadow-sm mb-6">
            <p class="text-xs text-gray-500">মোট পেমেন্ট (সব ফিল্টার)</p>
            <p class="text-2xl font-bold text-green-600">৳{{ number_format($total, 2) }}</p>
        </div>

        <form method="GET" class="bg-white rounded-2xl p-4 shadow-sm mb-6 flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="পেমেন্ট / সাপ্লায়ার খুঁজুন..." class="flex-1 min-w-[180px] border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 text-sm">
            <select name="supplier_id" class="border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 text-sm">
                <option value="">সব সাপ্লায়ার</option>
                @foreach($suppliers as $s)
                    <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
            <input type="date" name="from" value="{{ request('from') }}" class="border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 text-sm">
            <input type="date" name="to" value="{{ request('to') }}" class="border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 text-sm">
            <button type="submit" class="px-5 py-2 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition text-sm">ফিল্টার</button>
            <a href="{{ route('purchase.payments.index') }}" class="px-4 py-2 border rounded-xl text-gray-600 hover:bg-gray-50 transition text-sm">রিসেট</a>
        </form>

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
                        <th class="px-6 py-4 font-medium">পেমেন্ট</th>
                        <th class="px-6 py-4 font-medium">সাপ্লায়ার</th>
                        <th class="px-6 py-4 font-medium">বিল</th>
                        <th class="px-6 py-4 font-medium">তারিখ</th>
                        <th class="px-6 py-4 font-medium">মাধ্যম</th>
                        <th class="px-6 py-4 font-medium text-right">পরিমাণ</th>
                        <th class="px-6 py-4 font-medium">স্ট্যাটাস</th>
                        <th class="px-6 py-4 font-medium text-right">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($payments as $payment)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $payment->payment_number }}</td>
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('purchase.suppliers.show', $payment->supplier) }}" class="text-purple-600 hover:underline">{{ $payment->supplier->name }}</a>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @if($payment->invoice)
                                <a href="{{ route('purchase.invoices.show', $payment->invoice) }}" class="text-purple-600 hover:underline">{{ $payment->invoice->invoice_number }}</a>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $payment->payment_date->format('d M Y') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $payment->methodName() }}</td>
                        <td class="px-6 py-4 text-right font-semibold text-green-600">৳{{ number_format($payment->amount, 2) }}</td>
                        <td class="px-6 py-4">
                            @if($payment->status === 'completed')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Completed</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Cancelled</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if($payment->status === 'completed')
                                <form method="POST" action="{{ route('purchase.payments.destroy', $payment) }}" onsubmit="return confirm('পেমেন্ট বাতিল করবেন?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium">বাতিল</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-6 py-12 text-center text-gray-500">কোনো পেমেন্ট নেই</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($payments->hasPages())<div class="px-6 py-4 border-t">{{ $payments->withQueryString()->links() }}</div>@endif
        </div>
    </div>
</div>
@endsection
