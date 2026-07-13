@extends('layouts.tenant')

@section('title', $product->name.' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $product->name }}</h1>
                    <p class="text-gray-600">{{ $product->sku ?? __('products.no_sku') }}</p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('inventory.products.edit', $product) }}" class="bg-blue-600 text-white px-4 py-2 rounded-xl font-medium hover:bg-blue-700 transition">@lang('common.edit')</a>
                    <a href="{{ route('inventory.products.index') }}" class="text-gray-600 hover:text-purple-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Main --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Images --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">@lang('products.images')</h2>
                    @if($product->images->count() > 0)
                        <div class="grid grid-cols-4 gap-4">
                            @foreach($product->images as $image)
                                <div class="relative">
                                    <img src="{{ asset('storage/' . $image->image_path) }}" class="w-full h-24 object-cover rounded-lg {{ $image->is_primary ? 'ring-2 ring-purple-500' : '' }}">
                                    @if($image->is_primary)
                                        <span class="absolute top-1 left-1 bg-purple-600 text-white text-xs px-1.5 py-0.5 rounded">@lang('products.primary')</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500">@lang('products.no_images')</p>
                    @endif
                </div>

                {{-- Description --}}
                @if($product->description)
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">@lang('products.description')</h2>
                    <p class="text-gray-600 whitespace-pre-wrap">{{ $product->description }}</p>
                </div>
                @endif

                {{-- Attributes --}}
                @if($product->attributeValues->count() > 0)
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">@lang('products.attributes')</h2>
                    <div class="grid grid-cols-2 gap-4">
                        @foreach($product->attributeValues as $attr)
                            <div class="bg-gray-50 rounded-xl p-3">
                                <p class="text-xs text-gray-500 uppercase">{{ $attr->attributeTemplate->name ?? 'N/A' }}</p>
                                <p class="text-sm font-medium text-gray-900">{{ $attr->value }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Price --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">@lang('products.pricing')</h2>
                    <div class="text-3xl font-bold text-purple-600">{{ number_format($product->price, 2) }} BDT</div>
                    @if($product->cost_price)
                        <p class="text-sm text-gray-500 mt-1">@lang('products.cost'): {{ number_format($product->cost_price, 2) }} BDT</p>
                    @endif
                </div>

                {{-- Status --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">@lang('products.details')</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">@lang('products.stock')</span>
                            <span class="text-sm font-medium {{ $product->stock_quantity <= $product->low_stock_threshold ? 'text-red-600' : 'text-gray-900' }}">{{ $product->stock_quantity }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">@lang('products.status')</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $product->is_active ? __('common.active') : __('common.inactive') }}
                            </span>
                        </div>
                        @if($product->category)
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">@lang('products.category')</span>
                            <span class="text-sm font-medium text-gray-900">{{ $product->category->name }}</span>
                        </div>
                        @endif
                        @if($product->brand)
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">@lang('products.brand')</span>
                            <span class="text-sm font-medium text-gray-900">{{ $product->brand->name }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Variants --}}
                @if($product->variants->count() > 0)
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">@lang('products.variants')</h2>
                    <div class="space-y-2">
                        @foreach($product->variants as $variant)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $variant->name ?? $variant->sku }}</p>
                                    <p class="text-xs text-gray-500">{{ $variant->sku }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-gray-900">{{ number_format($variant->price ?? $product->price, 2) }}</p>
                                    <p class="text-xs {{ $variant->stock_quantity <= 0 ? 'text-red-600' : 'text-gray-500' }}">{{ $variant->stock_quantity }} @lang('products.in_stock')</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
