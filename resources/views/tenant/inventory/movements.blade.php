@extends('layouts.tenant')

@section('title', 'স্টক মুভমেন্ট - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">স্টক মুভমেন্ট হিস্ট্রি</h1>
                    <p class="text-gray-600">সব স্টক ইন/আউট/অ্যাডজাস্টমেন্ট দেখুন</p>
                </div>
                <a href="{{ route('inventory.index') }}" class="text-gray-600 hover:text-purple-600 font-medium">← ফিরে যান</a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @include('tenant.partials._nav-tabs', ['activePage' => 'inventory'])

        {{-- Inventory Sub-Navigation --}}
        <div class="mb-6 flex flex-wrap gap-2">
            <a href="{{ route('inventory.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white text-gray-700 border hover:bg-purple-50 hover:text-purple-600 transition">সারাংশ</a>
            <a href="{{ route('inventory.products.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white text-gray-700 border hover:bg-purple-50 hover:text-purple-600 transition">প্রোডাক্ট</a>
            <a href="{{ route('inventory.categories.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white text-gray-700 border hover:bg-purple-50 hover:text-purple-600 transition">ক্যাটাগরি</a>
            <a href="{{ route('inventory.brands.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white text-gray-700 border hover:bg-purple-50 hover:text-purple-600 transition">ব্র্যান্ড</a>
            <a href="{{ route('inventory.attributes.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white text-gray-700 border hover:bg-purple-50 hover:text-purple-600 transition">অ্যাট্রিবিউট</a>
            <a href="{{ route('inventory.warehouses.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white text-gray-700 border hover:bg-purple-50 hover:text-purple-600 transition">গুদম</a>
            <a href="{{ route('inventory.movements') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-purple-600 text-white">মুভমেন্ট</a>
            <a href="{{ route('inventory.alerts') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white text-gray-700 border hover:bg-purple-50 hover:text-purple-600 transition">অ্যালার্ট</a>
        </div>

        {{-- Filters --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <select name="product_id" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 text-sm">
                        <option value="">সব প্রোডাক্ট</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="warehouse_id" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 text-sm">
                        <option value="">সব গুদম</option>
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}" {{ request('warehouse_id') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="type" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 text-sm">
                        <option value="">সব ধরন</option>
                        <option value="in" {{ request('type') === 'in' ? 'selected' : '' }}>স্টক ইন</option>
                        <option value="out" {{ request('type') === 'out' ? 'selected' : '' }}>স্টক আউট</option>
                        <option value="adjustment" {{ request('type') === 'adjustment' ? 'selected' : '' }}>অ্যাডজাস্টমেন্ট</option>
                    </select>
                </div>
                <div>
                    <input type="date" name="from_date" value="{{ request('from_date') }}" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 text-sm" placeholder="থেকে">
                </div>
                <div class="flex items-center space-x-2">
                    <input type="date" name="to_date" value="{{ request('to_date') }}" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 text-sm">
                    <button type="submit" class="bg-gray-100 text-gray-700 px-3 py-2 rounded-xl text-sm font-medium hover:bg-gray-200 whitespace-nowrap">ফিল্টার</button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-left text-sm text-gray-500">
                        <th class="px-6 py-4 font-medium">সময়</th>
                        <th class="px-6 py-4 font-medium">ধরন</th>
                        <th class="px-6 py-4 font-medium">প্রোডাক্ট</th>
                        <th class="px-6 py-4 font-medium">গুদম</th>
                        <th class="px-6 py-4 font-medium">পরিমাণ</th>
                        <th class="px-6 py-4 font-medium">রেফারেন্স</th>
                        <th class="px-6 py-4 font-medium">বাই কারা</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($movements as $movement)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $movement->created_at->format('d M Y, h:i A') }}</td>
                        <td class="px-6 py-4">
                            @if($movement->type === 'in')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">স্টক ইন</span>
                            @elseif($movement->type === 'out')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">স্টক আউট</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">অ্যাডজাস্ট</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900 font-medium">{{ $movement->product->name ?? 'ডিলিট করা হয়েছে' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $movement->warehouse->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm font-semibold {{ $movement->type === 'in' ? 'text-green-600' : ($movement->type === 'out' ? 'text-red-600' : 'text-blue-600') }}">
                            {{ $movement->quantity_display }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $movement->reference ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $movement->creator->name ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-6 py-12 text-center text-gray-500">কোনো মুভমেন্ট নেই</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($movements->hasPages())<div class="px-6 py-4 border-t">{{ $movements->withQueryString()->links() }}</div>@endif
        </div>
    </div>
</div>
@endsection
