@extends('layouts.app')

@section('title', 'অ্যাট্রিবিউট টেমপ্লেট - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">অ্যাট্রিবিউট টেমপ্লেট</h1>
                    <p class="text-gray-600">ক্যাটাগরি অনুযায়ী ডায়নামিক অ্যাট্রিবিউট পরিচালনা করুন</p>
                </div>
                <a href="{{ route('inventory.attributes.create') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    নতুন অ্যাট্রিবিউট
                </a>
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
            <a href="{{ route('inventory.attributes.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-purple-600 text-white">অ্যাট্রিবিউট</a>
            <a href="{{ route('inventory.warehouses.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white text-gray-700 border hover:bg-purple-50 hover:text-purple-600 transition">গুদম</a>
            <a href="{{ route('inventory.movements') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white text-gray-700 border hover:bg-purple-50 hover:text-purple-600 transition">মুভমেন্ট</a>
            <a href="{{ route('inventory.alerts') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white text-gray-700 border hover:bg-purple-50 hover:text-purple-600 transition">অ্যালার্ট</a>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-sm mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <select name="category_id" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                        <option value="">সব ক্যাটাগরি</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="global_only" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                        <option value="">সব অ্যাট্রিবিউট</option>
                        <option value="1" {{ request('global_only') == '1' ? 'selected' : '' }}>শুধু গ্লোবাল</option>
                    </select>
                </div>
                <div class="flex items-center">
                    <button type="submit" class="w-full bg-gray-100 text-gray-700 px-4 py-2 rounded-xl font-medium hover:bg-gray-200 transition">ফিল্টার</button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-left text-sm text-gray-500">
                        <th class="px-6 py-4 font-medium">নাম</th>
                        <th class="px-6 py-4 font-medium">ক্যাটাগরি</th>
                        <th class="px-6 py-4 font-medium">ধরন</th>
                        <th class="px-6 py-4 font-medium">অপশন</th>
                        <th class="px-6 py-4 font-medium">আবশ্যিক</th>
                        <th class="px-6 py-4 font-medium text-right">কাজ</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($attributes as $attr)
                    <tr class="hover:bg-gray-50 {{ $attr->is_global ? 'bg-purple-50/50' : '' }}">
                        <td class="px-6 py-4 font-medium text-gray-900">
                            {{ $attr->name }}
                            @if($attr->is_global)
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700">গ্লোবাল</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $attr->display_category_name }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">{{ $attr->type }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $attr->options ? implode(', ', $attr->options) : '-' }}</td>
                        <td class="px-6 py-4">
                            @if($attr->is_required)<span class="text-green-600">✓</span>@else<span class="text-gray-400">—</span>@endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ route('inventory.attributes.edit', $attr) }}" class="text-gray-400 hover:text-blue-600 p-1"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                                <form action="{{ route('inventory.attributes.destroy', $attr) }}" method="POST" onsubmit="return confirm('নিশ্চিত?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-gray-400 hover:text-red-600 p-1"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500">কোনো অ্যাট্রিবিউট নেই</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($attributes->hasPages())<div class="px-6 py-4 border-t">{{ $attributes->withQueryString()->links() }}</div>@endif
        </div>
    </div>
</div>
@endsection
