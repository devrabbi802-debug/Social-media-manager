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
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-gray-900">প্রোডাক্ট ইমেজ</h2>
                        <div class="flex items-center space-x-3">
                            @if($product->images->count())
                                <span class="text-sm text-gray-500">{{ $product->images->count() }}টি ইমেজ</span>
                            @endif
                            @if($product->images->count() > 0)
                                <form action="{{ route('inventory.products.generate-embeddings', $product) }}" method="POST" class="inline" onsubmit="return confirm('এই প্রোডাক্টের সব ছবি AI দিয়ে চেনা হবে। চালিয়ে যেতে চান?')">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                        </svg>
                                        AI দিয়ে ছবি চেনান
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    @if($product->images->count())
                        {{-- Main Image --}}
                        <div class="relative mb-4">
                            <div id="main-image-container" class="relative overflow-hidden rounded-xl bg-gray-100">
                                @php $sortedImages = $product->images->sortBy('sort_order'); @endphp
                                <img id="main-image" src="{{ asset('storage/' . $sortedImages->first()->image_path) }}"
                                    alt="{{ $sortedImages->first()->alt_text ?? $product->name }}"
                                    class="w-full h-80 object-contain cursor-pointer hover:opacity-95 transition"
                                    onclick="openLightbox(0)">

                                {{-- Image Counter --}}
                                <div class="absolute top-3 right-3 bg-black/60 text-white text-xs px-2 py-1 rounded-full">
                                    <span id="current-index">1</span> / {{ $product->images->count() }}
                                </div>

                                {{-- Navigation Arrows --}}
                                @if($product->images->count() > 1)
                                    <button type="button" onclick="prevImage()" class="absolute left-3 top-1/2 -translate-y-1/2 bg-white/90 hover:bg-white text-gray-800 rounded-full w-10 h-10 flex items-center justify-center shadow-lg transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                    </button>
                                    <button type="button" onclick="nextImage()" class="absolute right-3 top-1/2 -translate-y-1/2 bg-white/90 hover:bg-white text-gray-800 rounded-full w-10 h-10 flex items-center justify-center shadow-lg transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </button>
                                @endif

                                {{-- AI Badge --}}
                                @if($sortedImages->first()->hasAnalysis())
                                    <div class="absolute bottom-3 left-3 bg-green-500 text-white text-xs px-2 py-1 rounded-full flex items-center">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        AI বিশ্লেষিত
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Thumbnails --}}
                        @if($product->images->count() > 1)
                            <div class="flex gap-2 overflow-x-auto pb-2">
                                @foreach($sortedImages as $index => $image)
                                    <button type="button" onclick="changeImage({{ $index }})"
                                        class="thumbnail-btn flex-shrink-0 w-20 h-20 rounded-lg overflow-hidden border-2 {{ $index === 0 ? 'border-purple-500' : 'border-transparent hover:border-gray-300' }} transition"
                                        data-index="{{ $index }}">
                                        <img src="{{ asset('storage/' . $image->image_path) }}"
                                            alt="{{ $image->alt_text ?? $product->name }}"
                                            class="w-full h-full object-cover">
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        {{-- Hidden array for JS --}}
                        <script>
                        const images = @json($sortedImages->map(fn($img) => [
                            'url' => asset('storage/' . $img->image_path),
                            'alt' => $img->alt_text ?? $product->name,
                            'analysis' => $img->hasAnalysis() ? $img->analysis_summary : null
                        ])->values());
                        let currentIndex = 0;

                        function changeImage(index) {
                            currentIndex = index;
                            document.getElementById('main-image').src = images[index].url;
                            document.getElementById('current-index').textContent = index + 1;

                            document.querySelectorAll('.thumbnail-btn').forEach((btn, i) => {
                                btn.classList.toggle('border-purple-500', i === index);
                                btn.classList.toggle('border-transparent', i !== index);
                            });
                        }

                        function nextImage() {
                            changeImage((currentIndex + 1) % images.length);
                        }

                        function prevImage() {
                            changeImage((currentIndex - 1 + images.length) % images.length);
                        }

                        function openLightbox(index) {
                            currentIndex = index;
                            const lightbox = document.getElementById('lightbox');
                            const lightboxImg = document.getElementById('lightbox-img');
                            const lightboxCounter = document.getElementById('lightbox-counter');
                            const lightboxAnalysis = document.getElementById('lightbox-analysis');

                            lightboxImg.src = images[index].url;
                            lightboxCounter.textContent = (index + 1) + ' / ' + images.length;
                            lightboxAnalysis.textContent = images[index].analysis || '';
                            lightbox.classList.remove('hidden');
                            document.body.style.overflow = 'hidden';
                        }

                        function closeLightbox() {
                            document.getElementById('lightbox').classList.add('hidden');
                            document.body.style.overflow = '';
                        }

                        function lightboxPrev() {
                            changeImage((currentIndex - 1 + images.length) % images.length);
                            openLightbox(currentIndex);
                        }

                        function lightboxNext() {
                            changeImage((currentIndex + 1) % images.length);
                            openLightbox(currentIndex);
                        }

                        // Keyboard navigation
                        document.addEventListener('keydown', (e) => {
                            if (document.getElementById('lightbox').classList.contains('hidden')) return;
                            if (e.key === 'Escape') closeLightbox();
                            if (e.key === 'ArrowLeft') lightboxPrev();
                            if (e.key === 'ArrowRight') lightboxNext();
                        });
                        </script>

                    @else
                        <div class="text-center py-12 text-gray-400">
                            <svg class="mx-auto h-16 w-16 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p>কোনো ইমেজ নেই</p>
                            <a href="{{ route('inventory.products.edit', $product) }}" class="text-purple-600 text-sm hover:underline">এডিটে ইমেজ যোগ করুন</a>
                        </div>
                    @endif
                </div>

                {{-- Lightbox --}}
                <div id="lightbox" class="hidden fixed inset-0 z-50 bg-black/90 flex items-center justify-center">
                    <button type="button" onclick="closeLightbox()" class="absolute top-4 right-4 text-white/80 hover:text-white z-10">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>

                    <button type="button" onclick="lightboxPrev()" class="absolute left-4 top-1/2 -translate-y-1/2 text-white/80 hover:text-white bg-white/10 rounded-full p-2">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>

                    <div class="max-w-4xl max-h-[85vh] px-16">
                        <img id="lightbox-img" src="" class="max-h-[80vh] mx-auto object-contain rounded-lg">
                        <div class="text-center mt-3">
                            <span id="lightbox-counter" class="text-white/80 text-sm"></span>
                            <p id="lightbox-analysis" class="text-white/60 text-xs mt-1"></p>
                        </div>
                    </div>

                    <button type="button" onclick="lightboxNext()" class="absolute right-4 top-1/2 -translate-y-1/2 text-white/80 hover:text-white bg-white/10 rounded-full p-2">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>

                {{-- Description --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">বিবরণ</h2>
                    <p class="text-gray-700">{{ $product->description ?? 'কোনো বিবরণ নেই' }}</p>
                </div>

                {{-- Variants --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-gray-900">ভ্যারিয়েন্ট ({{ $product->variants->count() }})</h2>
                        @php
                            $variantImagesCount = 0;
                            foreach($product->variants as $variant) {
                                $variantImagesCount += $variant->images->count();
                            }
                        @endphp
                        @if($variantImagesCount > 0)
                            <form action="{{ route('inventory.products.generate-variant-embeddings', $product) }}" method="POST" class="inline" onsubmit="return confirm('এই প্রোডাক্টের সব ভ্যারিয়েন্টের ছবি AI দিয়ে চেনা হবে। চালিয়ে যেতে চান?')">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                    </svg>
                                    AI দিয়ে ভ্যারিয়েন্ট ছবি চেনান
                                </button>
                            </form>
                        @endif
                    </div>

                    @if($product->variants->count())
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 text-left text-gray-500">
                                    <th class="px-4 py-2 font-medium">ইমেজ</th>
                                    <th class="px-4 py-2 font-medium">নাম</th>
                                    <th class="px-4 py-2 font-medium">SKU</th>
                                    <th class="px-4 py-2 font-medium">মূল্য</th>
                                    <th class="px-4 py-2 font-medium">স্টক</th>
                                    <th class="px-4 py-2 font-medium">অ্যাট্রিবিউট</th>
                                    <th class="px-4 py-2 font-medium">অ্যাকশন</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach($product->variants as $variant)
                                <tr>
                                    <td class="px-4 py-2">
                                        @if($variant->images->count())
                                            <div class="flex gap-1">
                                                @foreach($variant->images->sortBy('sort_order')->take(3) as $img)
                                                    <img src="{{ asset('storage/' . $img->image_path) }}" class="w-10 h-10 object-cover rounded-lg border">
                                                @endforeach
                                                @if($variant->images->count() > 3)
                                                    <span class="w-10 h-10 flex items-center justify-center bg-gray-100 rounded-lg text-xs text-gray-500">+{{ $variant->images->count() - 3 }}</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-gray-400 text-xs">ছবি নেই</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 font-medium">{{ $variant->name ?? '-' }}</td>
                                    <td class="px-4 py-2 font-mono text-xs">{{ $variant->sku }}</td>
                                    <td class="px-4 py-2">৳{{ number_format($variant->effective_price, 2) }}</td>
                                    <td class="px-4 py-2">{{ $variant->stock_quantity }}</td>
                                    <td class="px-4 py-2">
                                        @foreach($variant->attributes as $key => $value)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700 mr-1">{{ ucfirst($key) }}: {{ $value }}</span>
                                        @endforeach
                                    </td>
                                    <td class="px-4 py-2">
                                        <div class="flex items-center space-x-2">
                                            <button type="button" onclick="deleteVariant({{ $product->id }}, {{ $variant->id }})" class="text-red-600 hover:text-red-800 text-xs font-medium">ডিলিট</button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-gray-500 text-sm">কোনো ভ্যারিয়েন্ট নেই।</p>
                    @endif
                </div>
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
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">
                                {{ $attrValue->attributeTemplate->name }}
                                @if($attrValue->attributeTemplate->is_global)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700 ml-1">গ্লোবাল</span>
                                @endif
                            </span>
                            <span class="font-medium text-gray-900">{{ $attrValue->value }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- AI Image Analysis --}}
                @if($product->images->where('image_analysis', '!=', null)->count() || $product->images->where('embedding', '!=', null)->count())
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">AI ইমেজ বিশ্লেষণ</h2>
                    <div class="space-y-4">
                        @foreach($product->images as $image)
                        <div class="border border-gray-200 rounded-xl p-3">
                            <div class="flex items-center justify-between mb-2">
                                <img src="{{ asset('storage/' . $image->image_path) }}" class="w-full h-24 object-cover rounded-lg">
                            </div>
                            
                            {{-- Embedding Status --}}
                            <div class="mb-2">
                                @if(!empty($image->embedding))
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        ছবি চেনা হয়েছে
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        ছবি চেনা হয়নি
                                    </span>
                                @endif
                            </div>

                            {{-- Image Analysis --}}
                            @if($image->image_analysis)
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
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- AI Variant Image Analysis --}}
                @php
                    $variantImagesWithAnalysis = collect();
                    $variantImagesWithEmbedding = collect();
                    foreach($product->variants as $variant) {
                        foreach($variant->images as $img) {
                            if($img->image_analysis) {
                                $variantImagesWithAnalysis->push($img);
                            }
                            if(!empty($img->embedding)) {
                                $variantImagesWithEmbedding->push($img);
                            }
                        }
                    }
                @endphp
                @if($variantImagesWithAnalysis->count() || $variantImagesWithEmbedding->count())
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">AI ভ্যারিয়েন্ট ইমেজ বিশ্লেষণ</h2>
                    <div class="space-y-4">
                        @foreach($product->variants->flatMap->images as $image)
                        <div class="border border-gray-200 rounded-xl p-3">
                            <div class="flex items-center gap-2 mb-2">
                                <img src="{{ asset('storage/' . $image->image_path) }}" class="w-16 h-16 object-cover rounded-lg">
                                <div>
                                    <span class="text-xs font-medium text-purple-600">{{ $image->variant->sku ?? '-' }}</span>
                                    <p class="text-xs text-gray-500">{{ $image->variant->display ?? '' }}</p>
                                </div>
                            </div>

                            {{-- Embedding Status --}}
                            <div class="mb-2">
                                @if(!empty($image->embedding))
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        ছবি চেনা হয়েছে
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        ছবি চেনা হয়নি
                                    </span>
                                @endif
                            </div>
                            
                            {{-- Image Analysis --}}
                            @if($image->image_analysis)
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
                            @endif
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

@push('scripts')
<script>
function deleteVariant(productId, variantId) {
    if (!confirm('এই ভ্যারিয়েন্ট ডিলিট করতে চান?')) return;

    fetch(`/inventory/products/${productId}/variants/${variantId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(err => alert('Error: ' + err.message));
}
</script>
@endpush
