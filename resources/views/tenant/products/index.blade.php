@extends('layouts.tenant')

@section('title', __('products.list_title').' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Header --}}
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">@lang('products.list_title')</h1>
                    <p class="text-gray-600">@lang('products.list_subtitle')</p>
                </div>
                <a href="{{ route('inventory.products.create') }}" class="bg-purple-600 text-white px-6 py-3 rounded-xl font-medium hover:bg-purple-700 transition shadow-sm">
                    @lang('products.add_new')
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @include('tenant.partials._nav-tabs', ['activePage' => 'products'])

        {{-- Success/Error --}}
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">{{ session('error') }}</div>
        @endif

        {{-- Filters --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm mb-6">
            <form action="{{ route('inventory.products.index') }}" method="GET" class="flex flex-wrap gap-4">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="@lang('products.search_placeholder')" class="flex-1 min-w-[200px] px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <select name="category_id" class="px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <option value="">@lang('products.all_categories')</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
                <select name="brand_id" class="px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <option value="">@lang('products.all_brands')</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="bg-gray-100 text-gray-700 px-6 py-2 rounded-xl font-medium hover:bg-gray-200 transition">@lang('common.filter')</button>
            </form>
        </div>

        {{-- Products Table --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            @if($products->count() > 0)
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">@lang('products.product')</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">@lang('products.sku')</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">@lang('products.price')</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">@lang('products.stock')</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">@lang('products.status')</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">@lang('common.actions')</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($products as $product)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        @if($product->primaryImage)
                                            <img src="{{ asset('storage/' . $product->primaryImage->image_path) }}" class="w-10 h-10 rounded-lg object-cover mr-3" alt="">
                                        @else
                                            <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center mr-3">
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                        @endif
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $product->name }}</p>
                                            <p class="text-sm text-gray-500">{{ $product->category->name ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 font-mono">{{ $product->sku }}</td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ number_format($product->price, 2) }} BDT</td>
                                <td class="px-6 py-4 text-sm {{ $product->stock_quantity <= $product->low_stock_threshold ? 'text-red-600 font-bold' : 'text-gray-600' }}">
                                    {{ $product->stock_quantity }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $product->is_active ? __('common.active') : __('common.inactive') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="{{ route('inventory.products.show', $product) }}" class="text-purple-600 hover:text-purple-800 p-1">@lang('common.view')</a>
                                        <a href="{{ route('inventory.products.edit', $product) }}" class="text-blue-600 hover:text-blue-800 p-1">@lang('common.edit')</a>
                                        <form action="{{ route('inventory.products.destroy', $product) }}" method="POST" onsubmit="return confirm('{{ __('products.delete_confirm') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 p-1">@lang('common.delete')</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-6 py-4 border-t">
                    {{ $products->withQueryString()->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">@lang('products.no_products')</h3>
                    <p class="text-gray-500 mb-4">@lang('products.no_products_desc')</p>
                    <a href="{{ route('inventory.products.create') }}" class="inline-flex items-center px-6 py-3 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition">
                        @lang('products.add_first')
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
