@extends('layouts.app')

@section('title', 'ইনভেন্টরি - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">ইনভেন্টরি ম্যানেজমেন্ট</h1>
                    <p class="text-gray-600">স্টক পরিচালনা ও ট্র্যাকিং</p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('inventory.movements') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition">স্টক মুভমেন্ট</a>
                    <a href="{{ route('inventory.products.create') }}" class="px-4 py-2 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition">+ নতুন প্রোডাক্ট</a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @include('dashboard.partials._nav-tabs', ['activePage' => 'inventory'])

        {{-- Inventory Sub-Navigation --}}
        <div class="mb-6 flex flex-wrap gap-2">
            <a href="{{ route('inventory.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-purple-600 text-white">সারাংশ</a>
            <a href="{{ route('inventory.products.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white text-gray-700 border hover:bg-purple-50 hover:text-purple-600 transition">প্রোডাক্ট</a>
            <a href="{{ route('inventory.categories.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white text-gray-700 border hover:bg-purple-50 hover:text-purple-600 transition">ক্যাটাগরি</a>
            <a href="{{ route('inventory.brands.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white text-gray-700 border hover:bg-purple-50 hover:text-purple-600 transition">ব্র্যান্ড</a>
            <a href="{{ route('inventory.attributes.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white text-gray-700 border hover:bg-purple-50 hover:text-purple-600 transition">অ্যাট্রিবিউট</a>
            <a href="{{ route('inventory.warehouses.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white text-gray-700 border hover:bg-purple-50 hover:text-purple-600 transition">গুদম</a>
            <a href="{{ route('inventory.transfers.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white text-gray-700 border hover:bg-purple-50 hover:text-purple-600 transition">ট্রান্সফার</a>
            <a href="{{ route('inventory.movements') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white text-gray-700 border hover:bg-purple-50 hover:text-purple-600 transition">মুভমেন্ট</a>
            <a href="{{ route('inventory.alerts') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white text-gray-700 border hover:bg-purple-50 hover:text-purple-600 transition">অ্যালার্ট</a>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">মোট প্রোডাক্ট</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $totalProducts }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">কম স্টক</p>
                        <p class="text-3xl font-bold text-orange-600">{{ $lowStockCount }}</p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">স্টক শেষ</p>
                        <p class="text-3xl font-bold text-red-600">{{ $outOfStockCount }}</p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Stock Actions --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm mb-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">দ্রুত স্টক পরিচালনা</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <form action="{{ route('inventory.stock-in') }}" method="POST" class="border border-green-200 rounded-xl p-4 bg-green-50">
                    @csrf
                    <h3 class="font-semibold text-green-800 mb-3">স্টক যোগ করুন (In)</h3>
                    <div class="space-y-2">
                        <select name="product_id" required class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
                            <option value="">প্রোডাক্ট নির্বাচন</option>
                            @foreach($allProducts as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }})</option>
                            @endforeach
                        </select>
                        <select name="warehouse_id" required class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
                            <option value="">গুদম নির্বাচন</option>
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}">{{ $w->name }}</option>
                            @endforeach
                        </select>
                        <input type="number" name="quantity" placeholder="পরিমাণ" min="1" required class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
                        <input type="text" name="reference" placeholder="রেফারেন্স (ঐচ্ছিক)" class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
                        <button type="submit" class="w-full bg-green-600 text-white rounded-lg px-3 py-1.5 text-sm font-medium hover:bg-green-700">স্টক যোগ করুন</button>
                    </div>
                </form>

                <form action="{{ route('inventory.stock-out') }}" method="POST" class="border border-red-200 rounded-xl p-4 bg-red-50">
                    @csrf
                    <h3 class="font-semibold text-red-800 mb-3">স্টক বের করুন (Out)</h3>
                    <div class="space-y-2">
                        <select name="product_id" required class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
                            <option value="">প্রোডাক্ট নির্বাচন</option>
                            @foreach($allProducts as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }})</option>
                            @endforeach
                        </select>
                        <select name="warehouse_id" required class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
                            <option value="">গুদম নির্বাচন</option>
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}">{{ $w->name }}</option>
                            @endforeach
                        </select>
                        <input type="number" name="quantity" placeholder="পরিমাণ" min="1" required class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
                        <input type="text" name="reference" placeholder="রেফারেন্স (ঐচ্ছিক)" class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
                        <button type="submit" class="w-full bg-red-600 text-white rounded-lg px-3 py-1.5 text-sm font-medium hover:bg-red-700">স্টক বের করুন</button>
                    </div>
                </form>

                <form action="{{ route('inventory.adjust-stock') }}" method="POST" class="border border-blue-200 rounded-xl p-4 bg-blue-50">
                    @csrf
                    <h3 class="font-semibold text-blue-800 mb-3">স্টক সমন্বয় (Adjust)</h3>
                    <div class="space-y-2">
                        <select name="product_id" required class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
                            <option value="">প্রোডাক্ট নির্বাচন</option>
                            @foreach($allProducts as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }})</option>
                            @endforeach
                        </select>
                        <select name="warehouse_id" required class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
                            <option value="">গুদম নির্বাচন</option>
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}">{{ $w->name }}</option>
                            @endforeach
                        </select>
                        <input type="number" name="quantity" placeholder="নতুন পরিমাণ" min="0" required class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
                        <input type="text" name="notes" placeholder="নোট (ঐচ্ছিক)" class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
                        <button type="submit" class="w-full bg-blue-600 text-white rounded-lg px-3 py-1.5 text-sm font-medium hover:bg-blue-700">সমন্বয় করুন</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="প্রোডাক্ট সার্চ..." class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <select name="status" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                        <option value="">সব স্ট্যাটাস</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>সক্রিয়</option>
                        <option value="out_of_stock" {{ request('status') === 'out_of_stock' ? 'selected' : '' }}>স্টক শেষ</option>
                    </select>
                </div>
                <div class="flex items-center">
                    <label class="flex items-center">
                        <input type="checkbox" name="low_stock" value="1" {{ request('low_stock') ? 'checked' : '' }} class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                        <span class="ml-2 text-sm text-gray-700">শুধু কম স্টক</span>
                    </label>
                </div>
                <div>
                    <button type="submit" class="w-full bg-gray-100 text-gray-700 px-4 py-2 rounded-xl font-medium hover:bg-gray-200 transition">ফিল্টার</button>
                </div>
            </form>
        </div>

        {{-- Products List --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-left text-sm text-gray-500">
                        <th class="px-6 py-4 font-medium">প্রোডাক্ট</th>
                        <th class="px-6 py-4 font-medium">ক্যাটাগরি</th>
                        <th class="px-6 py-4 font-medium">স্টক</th>
                        <th class="px-6 py-4 font-medium">স্ট্যাটাস</th>
                        <th class="px-6 py-4 font-medium">অ্যালার্ট</th>
                        <th class="px-6 py-4 font-medium text-right">কাজ</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($products as $product)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <a href="{{ route('inventory.products.show', $product) }}" class="font-medium text-gray-900 hover:text-purple-600">{{ $product->name }}</a>
                            <p class="text-xs text-gray-500">{{ $product->sku }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $product->category->name ?? '-' }}</td>
                        <td class="px-6 py-4">
                            <span class="font-semibold {{ $product->stock_quantity <= 0 ? 'text-red-600' : ($product->stock_quantity <= 10 ? 'text-orange-600' : 'text-green-600') }}">
                                {{ $product->stock_quantity }} {{ $product->unit }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($product->status === 'active')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">সক্রিয়</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">স্টক শেষ</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($product->inventoryAlert)
                                @if($product->stock_quantity <= $product->inventoryAlert->threshold)
                                    <span class="text-orange-600 text-sm font-medium">⚠️ {{ $product->inventoryAlert->threshold }} এর কম</span>
                                @else
                                    <span class="text-green-600 text-sm">OK ({{ $product->inventoryAlert->threshold }})</span>
                                @endif
                            @else
                                <span class="text-gray-400 text-sm">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('inventory.movements', ['product_id' => $product->id]) }}" class="text-purple-600 hover:text-purple-700 text-sm font-medium">মুভমেন্ট →</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500">কোনো প্রোডাক্ট নেই</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($products->hasPages())<div class="px-6 py-4 border-t">{{ $products->withQueryString()->links() }}</div>@endif
        </div>
    </div>
</div>
@endsection
