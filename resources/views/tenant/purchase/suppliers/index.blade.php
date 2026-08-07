@extends('layouts.tenant')

@section('title', __('sidebar.suppliers').' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">@lang('sidebar.suppliers')</h1>
                    <p class="text-gray-600">সাপ্লায়ার তালিকা ও ব্যালেন্স</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('purchase.suppliers.create') }}" class="px-4 py-2 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition">+ নতুন সাপ্লায়ার</a>
                    <a href="{{ route('purchase.payments.create') }}" class="px-4 py-2 bg-white text-purple-600 border border-purple-300 rounded-xl font-medium hover:bg-purple-50 transition">+ পেমেন্ট দিন</a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @include('tenant.purchase.partials._nav', ['current' => 'suppliers'])

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

        {{-- Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">মোট সাপ্লায়ার</p>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">সক্রিয়</p>
                <p class="text-2xl font-bold text-green-600">{{ $stats['active'] }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <p class="text-xs text-gray-500">মোট বকেয়া (আমাদের দেনা)</p>
                <p class="text-2xl font-bold text-orange-600">৳{{ number_format($stats['outstanding'], 2) }}</p>
            </div>
        </div>

        {{-- Filters --}}
        <form method="GET" class="bg-white rounded-2xl p-4 shadow-sm mb-6 flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="নাম / ফোন / কোম্পানি খুঁজুন..." class="flex-1 min-w-[200px] border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 text-sm">
            <select name="status" class="border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 text-sm">
                <option value="">সব স্ট্যাটাস</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            <button type="submit" class="px-5 py-2 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition text-sm">ফিল্টার</button>
            <a href="{{ route('purchase.suppliers.index') }}" class="px-4 py-2 border rounded-xl text-gray-600 hover:bg-gray-50 transition text-sm">রিসেট</a>
        </form>

        {{-- Table --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
                        <th class="px-6 py-4 font-medium">সাপ্লায়ার</th>
                        <th class="px-6 py-4 font-medium">ফোন</th>
                        <th class="px-6 py-4 font-medium">পেমেন্ট টার্ম</th>
                        <th class="px-6 py-4 font-medium text-right">মোট ক্রয়</th>
                        <th class="px-6 py-4 font-medium text-right">বকেয়া</th>
                        <th class="px-6 py-4 font-medium">স্ট্যাটাস</th>
                        <th class="px-6 py-4 font-medium text-right">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($suppliers as $supplier)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <a href="{{ route('purchase.suppliers.show', $supplier) }}" class="font-medium text-purple-600 hover:underline">{{ $supplier->name }}</a>
                            @if($supplier->company)
                                <p class="text-xs text-gray-500">{{ $supplier->company }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $supplier->phone ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $supplier->payment_terms ?? ($supplier->payment_term_days > 0 ? 'Net '.$supplier->payment_term_days : '—') }}</td>
                        <td class="px-6 py-4 text-right font-medium">৳{{ number_format($supplier->totalPurchases(), 2) }}</td>
                        <td class="px-6 py-4 text-right font-semibold {{ $supplier->balance() > 0 ? 'text-orange-600' : 'text-green-600' }}">
                            ৳{{ number_format($supplier->balance(), 2) }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $supplier->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                {{ ucfirst($supplier->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('purchase.suppliers.edit', $supplier) }}" class="text-purple-600 hover:text-purple-800 text-xs font-medium">Edit</a>
                                <a href="{{ route('purchase.payments.create', ['supplier_id' => $supplier->id]) }}" class="text-green-600 hover:text-green-800 text-xs font-medium">Pay</a>
                                @if(auth()->user()->hasPermission('suppliers', 'delete'))
                                <form action="{{ route('purchase.suppliers.destroy', $supplier) }}" method="POST" class="inline" onsubmit="return confirm('সাপ্লায়ার ডিলিট করবেন?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium">Delete</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-6 py-12 text-center text-gray-500">কোনো সাপ্লায়ার নেই</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($suppliers->hasPages())<div class="px-6 py-4 border-t">{{ $suppliers->withQueryString()->links() }}</div>@endif
        </div>
    </div>
</div>
@endsection
