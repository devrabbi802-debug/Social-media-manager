@extends('layouts.app')

@section('title', 'স্টক অ্যালার্ট - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">স্টক অ্যালার্ট</h1>
                    <p class="text-gray-600">কম স্টক অ্যালার্ট পরিচালনা করুন</p>
                </div>
                <a href="{{ route('inventory.index') }}" class="text-gray-600 hover:text-purple-600 font-medium">← ফিরে যান</a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @include('dashboard.partials._nav-tabs', ['activePage' => 'inventory'])

        {{-- Inventory Sub-Navigation --}}
        <div class="mb-6 flex flex-wrap gap-2">
            <a href="{{ route('inventory.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white text-gray-700 border hover:bg-purple-50 hover:text-purple-600 transition">সারাংশ</a>
            <a href="{{ route('inventory.products.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white text-gray-700 border hover:bg-purple-50 hover:text-purple-600 transition">প্রোডাক্ট</a>
            <a href="{{ route('inventory.categories.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white text-gray-700 border hover:bg-purple-50 hover:text-purple-600 transition">ক্যাটাগরি</a>
            <a href="{{ route('inventory.brands.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white text-gray-700 border hover:bg-purple-50 hover:text-purple-600 transition">ব্র্যান্ড</a>
            <a href="{{ route('inventory.attributes.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white text-gray-700 border hover:bg-purple-50 hover:text-purple-600 transition">অ্যাট্রিবিউট</a>
            <a href="{{ route('inventory.warehouses.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white text-gray-700 border hover:bg-purple-50 hover:text-purple-600 transition">গুদম</a>
            <a href="{{ route('inventory.movements') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white text-gray-700 border hover:bg-purple-50 hover:text-purple-600 transition">মুভমেন্ট</a>
            <a href="{{ route('inventory.alerts') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-purple-600 text-white">অ্যালার্ট</a>
        </div>

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-left text-sm text-gray-500">
                        <th class="px-6 py-4 font-medium">প্রোডাক্ট</th>
                        <th class="px-6 py-4 font-medium">ক্যাটাগরি</th>
                        <th class="px-6 py-4 font-medium">বর্তমান স্টক</th>
                        <th class="px-6 py-4 font-medium">থ্রেশহোল্ড</th>
                        <th class="px-6 py-4 font-medium">স্ট্যাটাস</th>
                        <th class="px-6 py-4 font-medium text-right">কাজ</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($alerts as $alert)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $alert->product->name ?? 'ডিলিট করা হয়েছে' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $alert->product->category->name ?? '-' }}</td>
                        <td class="px-6 py-4 font-semibold {{ ($alert->product->stock_quantity ?? 0) <= $alert->threshold ? 'text-red-600' : 'text-green-600' }}">
                            {{ $alert->product->stock_quantity ?? 0 }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $alert->threshold }}</td>
                        <td class="px-6 py-4">
                            @if($alert->isLowStock())
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">কম স্টক!</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">OK</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <form action="{{ route('inventory.alerts.destroy', $alert) }}" method="POST" class="inline" onsubmit="return confirm('নিশ্চিত?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-gray-400 hover:text-red-600 text-sm">ডিলিট</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500">কোনো অ্যালার্ট নেই</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($alerts->hasPages())<div class="px-6 py-4 border-t">{{ $alerts->withQueryString()->links() }}</div>@endif
        </div>
    </div>
</div>
@endsection
