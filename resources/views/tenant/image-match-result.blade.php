@extends('layouts.tenant')

@section('title', 'ছবি ম্যাচ ফলাফল')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">ম্যাচ ফলাফল</h1>
                    <p class="text-gray-600">আপনার ছবির সাথে সেরা ম্যাচগুলো দেখুন</p>
                </div>
                <a href="{{ route('image-match.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    ফিরে যান
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Uploaded Image -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">আপলোড করা ছবি</h2>
                    <img src="{{ $uploadedImage }}" alt="Uploaded" class="w-full h-auto rounded-lg shadow-md">
                </div>
            </div>

            <!-- Match Results -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">
                        ম্যাচ ফলাফল 
                        <span class="text-sm font-normal text-gray-500">({{ $totalCatalog }}টি প্রোডাক্টের মধ্যে)</span>
                    </h2>

                    @if(empty($matches))
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">কোনো ম্যাচ পাওয়া যায়নি</h3>
                            <p class="mt-1 text-sm text-gray-500">এই ছবির সাথে কোনো প্রোডাক্ট ম্যাচ করেনি।</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($matches as $index => $match)
                                @php
                                    $productId = $match['product_id'] ?? null;
                                    $productUrl = $productId ? route('inventory.products.show', $productId) : '#';
                                @endphp
                                <a href="{{ $productUrl }}" class="block border rounded-lg p-4 transition hover:shadow-md {{ $index === 0 ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-purple-300' }}">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-4">
                                            <div class="flex-shrink-0">
                                                @if($index === 0)
                                                    <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-green-500 text-white text-sm font-bold">
                                                        ১
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-gray-300 text-gray-700 text-sm font-bold">
                                                        {{ $index + 1 }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div>
                                                <h3 class="text-lg font-semibold text-gray-800">{{ $match['product_name'] }}</h3>
                                                @if(!empty($match['product_sku']))
                                                    <p class="text-sm text-gray-500">SKU: {{ $match['product_sku'] }}</p>
                                                @endif
                                                @if(!empty($match['product_price']))
                                                    <p class="text-sm font-medium text-purple-600">মূল্য: ৳{{ number_format($match['product_price'], 2) }}</p>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-2xl font-bold {{ $match['score'] >= 0.9 ? 'text-green-600' : ($match['score'] >= 0.7 ? 'text-yellow-600' : 'text-red-600') }}">
                                                {{ round($match['score'] * 100, 1) }}%
                                            </div>
                                            <div class="text-sm text-gray-500">স্কোর</div>
                                            @if($productId)
                                                <span class="inline-flex items-center mt-2 text-xs text-purple-600 font-medium">
                                                    প্রোডাক্ট দেখুন →
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    @if($match['score'] >= 0.9)
                                        <div class="mt-2 text-sm text-green-700">
                                            <strong>উচ্চ সম্ভাবনা:</strong> এই প্রোডাক্টটি প্রায় নিশ্চিতভাবেই একই!
                                        </div>
                                    @elseif($match['score'] >= 0.7)
                                        <div class="mt-2 text-sm text-yellow-700">
                                            <strong>মাঝারি সম্ভাবনা:</strong> এই প্রোডাক্টটি সম্ভবত একই, তবে নিশ্চিত করতে হবে।
                                        </div>
                                    @else
                                        <div class="mt-2 text-sm text-red-700">
                                            <strong>কম সম্ভাবনা:</strong> এই প্রোডাক্টটি সম্ভবত ভিন্ন।
                                        </div>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
