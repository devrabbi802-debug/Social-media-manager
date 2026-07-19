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
                                    <img src="{{ asset('storage/' . $image->image_path) }}" class="w-full h-24 object-cover rounded-lg">
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

                {{-- Category Extra Fields --}}
                @if($product->attributeValues->count() > 0)
                @php
                    $extraFieldValues = $product->attributeValues->filter(fn($av) =>
                        $av->attributeTemplate &&
                        !$av->attributeTemplate->is_global &&
                        !$av->attributeTemplate->is_variant_option
                    );
                    $otherAttributes = $product->attributeValues->filter(fn($av) =>
                        !$av->attributeTemplate ||
                        $av->attributeTemplate->is_global ||
                        $av->attributeTemplate->is_variant_option
                    );
                @endphp

                @if($extraFieldValues->count() > 0)
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-1">@lang('products.category_fields')</h2>
                    <p class="text-sm text-gray-500 mb-4">{{ $businessCategory?->name ?? '' }}</p>
                    <div class="grid grid-cols-2 gap-4">
                        @foreach($extraFieldValues as $attr)
                            <div class="bg-gray-50 rounded-xl p-3">
                                <p class="text-xs text-gray-500 uppercase">{{ $attr->attributeTemplate->name ?? 'N/A' }}</p>
                                @if($attr->attributeTemplate && $attr->attributeTemplate->type === 'boolean')
                                    <p class="text-sm font-medium {{ $attr->value === '1' ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $attr->value === '1' ? '✓ '.__('common.yes') : '✗ '.__('common.no') }}
                                    </p>
                                @else
                                    <p class="text-sm font-medium text-gray-900">{{ $attr->typed_value }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($otherAttributes->count() > 0)
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">@lang('products.attributes')</h2>
                    <div class="grid grid-cols-2 gap-4">
                        @foreach($otherAttributes as $attr)
                            <div class="bg-gray-50 rounded-xl p-3">
                                <p class="text-xs text-gray-500 uppercase">{{ $attr->attributeTemplate->name ?? 'N/A' }}</p>
                                <p class="text-sm font-medium text-gray-900">{{ $attr->typed_value }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
                @endif

                {{-- Variants --}}
                @if($product->variants->count() > 0)
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">@lang('products.variants') ({{ $product->variants->count() }})</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Attributes</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">@lang('products.price')</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">@lang('products.stock')</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">@lang('products.images')</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($product->variants as $variant)
                                <tr>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $variant->sku }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        @if(is_array($variant->attributes))
                                            @foreach($variant->attributes as $name => $value)
                                                <span class="inline-block bg-purple-100 text-purple-700 text-xs px-2 py-0.5 rounded-full mr-1">
                                                    {{ $name }}: {{ $value }}
                                                </span>
                                            @endforeach
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm font-bold text-gray-900">
                                        {{ number_format($variant->effective_price, 2) }} BDT
                                    </td>
                                    <td class="px-4 py-3 text-sm {{ $variant->stock_quantity <= 0 ? 'text-red-600 font-bold' : 'text-gray-900' }}">
                                        {{ $variant->stock_quantity }}
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($variant->images->count() > 0)
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($variant->images as $img)
                                                    <img src="{{ asset('storage/' . $img->image_path) }}" class="w-10 h-10 object-cover rounded-lg" alt="{{ $img->alt_text }}">
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400">@lang('products.no_image')</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Price --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">@lang('products.pricing')</h2>
                    @if($product->discount_price && $product->discount_price < $product->base_price)
                        <div class="text-3xl font-bold text-green-600">{{ number_format($product->discount_price, 2) }} BDT</div>
                        <p class="text-sm text-gray-400 line-through">{{ number_format($product->base_price, 2) }} BDT</p>
                        <p class="text-xs text-green-600 mt-1">
                            @lang('products.savings'): {{ number_format($product->base_price - $product->discount_price, 2) }} BDT
                        </p>
                    @else
                        <div class="text-3xl font-bold text-purple-600">{{ number_format($product->base_price, 2) }} BDT</div>
                    @endif
                </div>

                {{-- Details --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">@lang('products.details')</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">@lang('products.stock')</span>
                            <span class="text-sm font-medium {{ $product->total_stock <= 0 ? 'text-red-600' : 'text-gray-900' }}">
                                {{ $product->total_stock }}
                                @if($product->variants->count() > 0)
                                    <span class="text-xs text-gray-400">({{ $product->variants->count() }} variants)</span>
                                @endif
                            </span>
                        </div>
                        @if($product->unit)
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">@lang('products.unit')</span>
                            <span class="text-sm font-medium text-gray-900">{{ $product->unit }}</span>
                        </div>
                        @endif
                        @if($product->weight_kg)
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">@lang('products.weight_kg')</span>
                            <span class="text-sm font-medium text-gray-900">{{ $product->weight_kg }} kg</span>
                        </div>
                        @endif
                        @if($product->sku)
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">SKU</span>
                            <span class="text-sm font-medium text-gray-900">{{ $product->sku }}</span>
                        </div>
                        @endif
                        @if($product->barcode)
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">@lang('products.barcode')</span>
                            <span class="text-sm font-medium text-gray-900">{{ $product->barcode }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">@lang('products.status')</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $product->status === 'active' ? 'bg-green-100 text-green-800' :
                                   ($product->status === 'inactive' ? 'bg-gray-100 text-gray-800' : 'bg-red-100 text-red-800') }}">
                                @lang('products.status_'.$product->status)
                            </span>
                        </div>
                        @if($product->is_featured)
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">@lang('products.featured')</span>
                            <span class="text-sm font-medium text-yellow-600">★</span>
                        </div>
                        @endif
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

                {{-- AI Recognition --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">AI</h2>
                    <form action="{{ route('inventory.products.generate-embeddings', $product) }}" method="POST"
                          onsubmit="return confirm('{{ __('products.ai_recognize_all_confirm') }}')">
                        @csrf
                        <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-xl font-medium hover:bg-indigo-700 transition text-sm">
                            @lang('products.ai_recognize')
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
