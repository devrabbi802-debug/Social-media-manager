@extends('layouts.tenant')

@section('title', __('inventory.movement_title').' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">@lang('inventory.movement_title')</h1>
                    <p class="text-gray-600">@lang('inventory.movement_subtitle')</p>
                </div>
                <a href="{{ route('inventory.index') }}" class="text-gray-600 hover:text-purple-600 font-medium">← @lang('common.back')</a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @include('tenant.partials._nav-tabs', ['activePage' => 'inventory'])

        {{-- Inventory Sub-Navigation --}}
        <div class="mb-6 flex flex-wrap gap-2">
            <a href="{{ route('inventory.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white text-gray-700 border hover:bg-purple-50 hover:text-purple-600 transition">@lang('common.summary')</a>
            <a href="{{ route('inventory.products.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white text-gray-700 border hover:bg-purple-50 hover:text-purple-600 transition">@lang('products.list_title')</a>
            <a href="{{ route('inventory.categories.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white text-gray-700 border hover:bg-purple-50 hover:text-purple-600 transition">@lang('categories.list_title')</a>
            <a href="{{ route('inventory.brands.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white text-gray-700 border hover:bg-purple-50 hover:text-purple-600 transition">@lang('brands.list_title')</a>
            <a href="{{ route('inventory.attributes.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white text-gray-700 border hover:bg-purple-50 hover:text-purple-600 transition">@lang('attributes.list_title')</a>
            <a href="{{ route('inventory.warehouses.index') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white text-gray-700 border hover:bg-purple-50 hover:text-purple-600 transition">@lang('warehouses.list_title')</a>
            <a href="{{ route('inventory.movements') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-purple-600 text-white">@lang('inventory.stock_movement')</a>
            <a href="{{ route('inventory.alerts') }}" class="px-4 py-2 rounded-xl text-sm font-medium bg-white text-gray-700 border hover:bg-purple-50 hover:text-purple-600 transition">@lang('inventory.alert_title')</a>
        </div>

        {{-- Filters --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <select name="product_id" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 text-sm">
                        <option value="">@lang('inventory.all_products')</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="warehouse_id" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 text-sm">
                        <option value="">@lang('inventory.all_warehouses')</option>
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}" {{ request('warehouse_id') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="type" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 text-sm">
                        <option value="">@lang('inventory.all_types')</option>
                        <option value="in" {{ request('type') === 'in' ? 'selected' : '' }}>@lang('common.stock_in')</option>
                        <option value="out" {{ request('type') === 'out' ? 'selected' : '' }}>@lang('common.stock_out')</option>
                        <option value="adjustment" {{ request('type') === 'adjustment' ? 'selected' : '' }}>@lang('common.adjustment')</option>
                    </select>
                </div>
                <div>
                    <input type="date" name="from_date" value="{{ request('from_date') }}" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 text-sm" placeholder="@lang('common.from')">
                </div>
                <div class="flex items-center space-x-2">
                    <input type="date" name="to_date" value="{{ request('to_date') }}" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 text-sm">
                    <button type="submit" class="bg-gray-100 text-gray-700 px-3 py-2 rounded-xl text-sm font-medium hover:bg-gray-200 whitespace-nowrap">@lang('common.filter')</button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-left text-sm text-gray-500">
                        <th class="px-6 py-4 font-medium">@lang('common.time')</th>
                        <th class="px-6 py-4 font-medium">@lang('products.type')</th>
                        <th class="px-6 py-4 font-medium">@lang('products.product')</th>
                        <th class="px-6 py-4 font-medium">@lang('warehouses.name')</th>
                        <th class="px-6 py-4 font-medium">@lang('inventory.quantity')</th>
                        <th class="px-6 py-4 font-medium">@lang('common.reference')</th>
                        <th class="px-6 py-4 font-medium">@lang('common.created_by')</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($movements as $movement)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $movement->created_at->format('d M Y, h:i A') }}</td>
                        <td class="px-6 py-4">
                            @if($movement->type === 'in')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">@lang('common.stock_in')</span>
                            @elseif($movement->type === 'out')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">@lang('common.stock_out')</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">@lang('common.adjustment')</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900 font-medium">{{ $movement->product->name ?? __('common.deleted') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $movement->warehouse->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm font-semibold {{ $movement->type === 'in' ? 'text-green-600' : ($movement->type === 'out' ? 'text-red-600' : 'text-blue-600') }}">
                            {{ $movement->quantity_display }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $movement->reference ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $movement->creator->name ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-6 py-12 text-center text-gray-500">@lang('common.no_data')</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($movements->hasPages())<div class="px-6 py-4 border-t">{{ $movements->withQueryString()->links() }}</div>@endif
        </div>
    </div>
</div>
@endsection
