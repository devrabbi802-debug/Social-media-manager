@extends('layouts.app')

@section('title', $product->name . ' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $product->name }}</h1>
                    <p class="text-gray-600">SKU: {{ $product->sku }}</p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('inventory.products.edit', $product) }}" class="px-4 py-2 bg-blue-600 text-white rounded-xl font-medium hover:bg-blue-700 transition">এডিট</a>
                    <a href="{{ route('inventory.products.index') }}" class="text-gray-600 hover:text-purple-600 font-medium">← ফিরে যান</a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid lg:grid-cols-3 gap-8">
            {{-- Left Column --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Images --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">প্রোডাক্ট ইমেজ</h2>
                    @if($product->images->count())
                        <div class="grid grid-cols-3 gap-4">
                            @foreach($product->images->sortBy('sort_order') as $image)
                            <div class="relative group">
                                <img src="{{ asset('storage/' . $image->image_path) }}" alt="{{ $image->alt_text ?? $product->name }}"
                                    class="w-full h-32 object-cover rounded-xl">
                                @if($image->hasAnalysis())
                                    <div class="absolute bottom-1 left-1 bg-green-500 text-white text-xs px-1.5 py-0.5 rounded flex items-center">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        AI বিশ্লেষিত
                                    </div>
                                @else
                                    <div class="absolute bottom-1 left-1 bg-yellow-500 text-white text-xs px-1.5 py-0.5 rounded">বিশ্লেষণ হচ্ছে...</div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500">কোনো ইমেজ নেই</p>
                    @endif
                </div>

                {{-- Description --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">বিবরণ</h2>
                    <p class="text-gray-700">{{ $product->description ?? 'কোনো বিবরণ নেই' }}</p>
                </div>

                {{-- Variants --}}
                @if($product->variants->count())
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">ভ্যারিয়েন্ট ({{ $product->variants->count() }})</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 text-left text-gray-500">
                                    <th class="px-4 py-2 font-medium">নাম</th>
                                    <th class="px-4 py-2 font-medium">SKU</th>
                                    <th class="px-4 py-2 font-medium">মূল্য</th>
                                    <th class="px-4 py-2 font-medium">স্টক</th>
                                    <th class="px-4 py-2 font-medium">অ্যাট্রিবিউট</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach($product->variants as $variant)
                                <tr>
                                    <td class="px-4 py-2 font-medium">{{ $variant->name ?? '-' }}</td>
                                    <td class="px-4 py-2 font-mono">{{ $variant->sku }}</td>
                                    <td class="px-4 py-2">৳{{ number_format($variant->effective_price, 2) }}</td>
                                    <td class="px-4 py-2">{{ $variant->stock_quantity }}</td>
                                    <td class="px-4 py-2">
                                        @foreach($variant->attributes as $key => $value)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 mr-1">{{ ucfirst($key) }}: {{ $value }}</span>
                                        @endforeach
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>

            {{-- Right Column --}}
            <div class="space-y-6">
                {{-- Price Card --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">মূল্য</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">মূল মূল্য</span>
                            <span class="font-semibold">৳{{ number_format($product->base_price, 2) }}</span>
                        </div>
                        @if($product->discount_price)
                        <div class="flex justify-between">
                            <span class="text-gray-600">ডিসকাউন্ট মূল্য</span>
                            <span class="font-semibold text-purple-600">৳{{ number_format($product->discount_price, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">সাশ্রয়</span>
                            <span class="text-green-600">৳{{ number_format($product->base_price - $product->discount_price, 2) }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Stock Card --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">স্টক</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">বর্তমান স্টক</span>
                            <span class="font-semibold {{ $product->stock_quantity <= 0 ? 'text-red-600' : ($product->stock_quantity <= 10 ? 'text-orange-600' : 'text-green-600') }}">
                                {{ $product->stock_quantity }} {{ $product->unit }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">স্ট্যাটাস</span>
                            @if($product->status === 'active')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">সক্রিয়</span>
                            @elseif($product->status === 'inactive')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">নিষ্ক্রিয়</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">স্টক শেষ</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Dynamic Attributes --}}
                @if($product->attributeValues->count())
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">কাস্টম অ্যাট্রিবিউট</h2>
                    <div class="space-y-3">
                        @foreach($product->attributeValues as $attrValue)
                        <div class="flex justify-between">
                            <span class="text-gray-600">{{ $attrValue->attributeTemplate->name }}</span>
                            <span class="font-medium text-gray-900">{{ $attrValue->value }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- AI Image Analysis --}}
                @if($product->images->where('image_analysis', '!=', null)->count())
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">AI ইমেজ বিশ্লেষণ</h2>
                    <div class="space-y-4">
                        @foreach($product->images->where('image_analysis', '!=', null) as $image)
                        <div class="border border-gray-200 rounded-xl p-3">
                            <img src="{{ asset('storage/' . $image->image_path) }}" class="w-full h-24 object-cover rounded-lg mb-2">
                            <div class="space-y-1">
                                @foreach($image->image_analysis as $key => $value)
                                    @if($value)
                                    <div class="flex justify-between text-xs">
                                        <span class="text-gray-500">{{ ucfirst(str_replace('_', ' ', $key)) }}</span>
                                        <span class="text-gray-900">{{ $value }}</span>
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Info --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">তথ্য</h2>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">তৈরি</span>
                            <span class="text-gray-900">{{ $product->created_at->format('d M Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">আপডেট</span>
                            <span class="text-gray-900">{{ $product->updated_at->format('d M Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">ফিচার্ড</span>
                            <span class="text-gray-900">{{ $product->is_featured ? 'হ্যাঁ' : 'না' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
