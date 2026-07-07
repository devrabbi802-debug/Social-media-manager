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
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-gray-900">ভ্যারিয়েন্ট ({{ $product->variants->count() }})</h2>
                        <button type="button" onclick="openVariantModal()" class="px-4 py-2 bg-green-600 text-white rounded-xl text-sm font-medium hover:bg-green-700 transition">+ ভ্যারিয়েন্ট যোগ করুন</button>
                    </div>

                    @if($product->variants->count())
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 text-left text-gray-500">
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
                                            <button type="button" onclick='editVariant(@json($variant))' class="text-blue-600 hover:text-blue-800 text-xs font-medium">এডিট</button>
                                            <form action="{{ route('inventory.products.variants.destroy', [$product, $variant]) }}" method="POST" class="inline" onsubmit="return confirm('এই ভ্যারিয়েন্ট ডিলিট করতে চান?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium">ডিলিট</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-gray-500 text-sm">কোনো ভ্যারিয়েন্ট নেই। উপরের বাটনে ক্লিক করে যোগ করুন।</p>
                    @endif
                </div>

                {{-- Variant Modal --}}
                <div id="variant-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
                    <div class="bg-white rounded-2xl p-6 w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
                        <div class="flex items-center justify-between mb-4">
                            <h3 id="variant-modal-title" class="text-lg font-bold text-gray-900">ভ্যারিয়েন্ট যোগ করুন</h3>
                            <button type="button" onclick="closeVariantModal()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
                        </div>

                        <form id="variant-form" method="POST">
                            @csrf
                            <div id="variant-method-field"></div>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">ভ্যারিয়েন্ট নাম</label>
                                    <input type="text" name="name" id="variant-name" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500" placeholder="যেমন: Medium / Black">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">SKU *</label>
                                    <input type="text" name="sku" id="variant-sku" required class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">মূল্য (৳)</label>
                                    <input type="number" name="price" id="variant-price" step="0.01" min="0" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                                    <p class="text-xs text-gray-500 mt-1">খালি রাখলে মূল মূল্য ব্যবহার হবে</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">স্টক পরিমাণ *</label>
                                    <input type="number" name="stock_quantity" id="variant-stock" required min="0" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">বারকোড</label>
                                    <input type="text" name="barcode" id="variant-barcode" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">অ্যাট্রিবিউট *</label>
                                    <div id="variant-attributes-fields" class="space-y-2">
                                        <p class="text-xs text-gray-500">লোড হচ্ছে...</p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end space-x-3 mt-6">
                                <button type="button" onclick="closeVariantModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition">বাতিল</button>
                                <button type="submit" id="variant-submit-btn" class="px-6 py-2 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition">সংরক্ষণ</button>
                            </div>
                        </form>
                    </div>
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

@push('scripts')
<script>
const variantModal = document.getElementById('variant-modal');
const variantForm = document.getElementById('variant-form');
const variantMethodField = document.getElementById('variant-method-field');
const variantModalTitle = document.getElementById('variant-modal-title');
const variantSubmitBtn = document.getElementById('variant-submit-btn');
const variantAttributesFields = document.getElementById('variant-attributes-fields');
const categoryId = {{ $product->category_id }};

function openVariantModal() {
    variantForm.reset();
    variantMethodField.innerHTML = '';
    variantModalTitle.textContent = 'ভ্যারিয়েন্ট যোগ করুন';
    variantSubmitBtn.textContent = 'সংরক্ষণ';
    variantForm.action = '{{ route("inventory.products.variants.store", $product) }}';
    loadVariantAttributes();
    variantModal.classList.remove('hidden');
    variantModal.classList.add('flex');
}

function editVariant(variant) {
    variantForm.reset();
    variantMethodField.innerHTML = '@method("PUT")';
    variantModalTitle.textContent = 'ভ্যারিয়েন্ট এডিট করুন';
    variantSubmitBtn.textContent = 'আপডেট করুন';
    variantForm.action = '{{ route("inventory.products.variants.update", [$product, "__ID__"]) }}'.replace('__ID__', variant.id);

    document.getElementById('variant-name').value = variant.name || '';
    document.getElementById('variant-sku').value = variant.sku || '';
    document.getElementById('variant-price').value = variant.price || '';
    document.getElementById('variant-stock').value = variant.stock_quantity || 0;
    document.getElementById('variant-barcode').value = variant.barcode || '';

    loadVariantAttributes(variant.attributes);

    variantModal.classList.remove('hidden');
    variantModal.classList.add('flex');
}

function closeVariantModal() {
    variantModal.classList.add('hidden');
    variantModal.classList.remove('flex');
}

function loadVariantAttributes(selectedAttributes = {}) {
    fetch('{{ route("inventory.products.attributes") }}?category_id=' + categoryId)
        .then(r => r.json())
        .then(attributes => {
            if (!attributes.length) {
                variantAttributesFields.innerHTML = '<p class="text-xs text-gray-500">এই ক্যাটাগরিতে কোনো অ্যাট্রিবিউট নেই</p>';
                return;
            }
            variantAttributesFields.innerHTML = attributes.map(attr => {
                const selectedVal = selectedAttributes[attr.slug] || '';
                let input = '';
                if (attr.type === 'select') {
                    const options = (attr.options || []).map(o =>
                        `<option value="${o}" ${selectedVal === o ? 'selected' : ''}>${o}</option>`
                    ).join('');
                    input = `<select name="attributes[${attr.slug}]" required class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500"><option value="">নির্বাচন করুন</option>${options}</select>`;
                } else {
                    input = `<input type="text" name="attributes[${attr.slug}]" value="${selectedVal}" required class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">`;
                }
                return `<div><label class="block text-xs font-medium text-gray-600 mb-1">${attr.name}</label>${input}</div>`;
            }).join('');
        });
}
</script>
@endpush
