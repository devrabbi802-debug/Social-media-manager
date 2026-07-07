@extends('layouts.app')

@section('title', 'এডিট - ' . $product->name . ' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">প্রোডাক্ট এডিট করুন</h1>
                    <p class="text-gray-600">{{ $product->name }}</p>
                </div>
                <a href="{{ route('inventory.products.index') }}" class="text-gray-600 hover:text-purple-600 font-medium">← ফিরে যান</a>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-2xl p-4 mb-6 flex items-center">
            <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <p class="text-green-800 font-medium">{{ session('success') }}</p>
        </div>
        @endif

        <form action="{{ route('inventory.products.update', $product) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf @method('PUT')

            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900 mb-4">মৌলিক তথ্য</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">প্রোডাক্টের নাম *</label>
                        <input type="text" name="name" value="{{ old('name', $product->name) }}" required class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">SKU *</label>
                        <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" required class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        @error('sku') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">বারকোড</label>
                        <input type="text" name="barcode" value="{{ old('barcode', $product->barcode) }}" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ক্যাটাগরি *</label>
                        <select name="category_id" id="category_id" required class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ব্র্যান্ড</label>
                        <select name="brand_id" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                            <option value="">ব্র্যান্ড নির্বাচন করুন</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">বিবরণ</label>
                        <textarea name="description" rows="3" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent">{{ old('description', $product->description) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900 mb-4">মূল্য ও স্টক</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">মূল মূল্য (৳) *</label>
                        <input type="number" name="base_price" value="{{ old('base_price', $product->base_price) }}" step="0.01" min="0" required class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                        @error('base_price') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ডিসকাউন্ট মূল্য (৳)</label>
                        <input type="number" name="discount_price" value="{{ old('discount_price', $product->discount_price) }}" step="0.01" min="0" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                        @error('discount_price') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">স্টক পরিমাণ</label>
                        <input type="number" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}" min="0" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">একক *</label>
                        <select name="unit" required class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                            @foreach(['pcs'=>'পিস','kg'=>'কেজি','liter'=>'লিটার','pack'=>'প্যাক','box'=>'বক্স','pair'=>'জোড়া','set'=>'সেট','meter'=>'মিটার'] as $val => $label)
                                <option value="{{ $val }}" {{ old('unit', $product->unit) === $val ? 'selected' : '' }}>{{ $label }} ({{ $val }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">স্ট্যাটাস</label>
                        <select name="status" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                            <option value="active" {{ old('status', $product->status) === 'active' ? 'selected' : '' }}>সক্রিয়</option>
                            <option value="inactive" {{ old('status', $product->status) === 'inactive' ? 'selected' : '' }}>নিষ্ক্রিয়</option>
                            <option value="out_of_stock" {{ old('status', $product->status) === 'out_of_stock' ? 'selected' : '' }}>স্টক শেষ</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ফিচার্ড</label>
                        <select name="is_featured" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                            <option value="0" {{ old('is_featured', $product->is_featured) ? '' : 'selected' }}>না</option>
                            <option value="1" {{ old('is_featured', $product->is_featured) ? 'selected' : '' }}>হ্যাঁ</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Dynamic Attributes --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm" id="attributes-section" @if(!$attributeTemplates->count()) style="display: none;" @endif>
                <h2 class="text-lg font-bold text-gray-900 mb-4">কাস্টম অ্যাট্রিবিউট</h2>
                <div id="attributes-container" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($attributeTemplates as $attr)
                        @php $val = $product->attributeValues->where('attribute_template_id', $attr->id)->first(); @endphp
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ $attr->name }} {{ $attr->is_required ? '*' : '' }}</label>
                            @if($attr->type === 'select')
                                <select name="attribute[{{ $attr->id }}]" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                                    <option value="">নির্বাচন করুন</option>
                                    @foreach($attr->options ?? [] as $opt)
                                        <option value="{{ $opt }}" {{ $val && $val->value === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                    @endforeach
                                </select>
                            @elseif($attr->type === 'boolean')
                                <select name="attribute[{{ $attr->id }}]" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                                    <option value="">নির্বাচন করুন</option>
                                    <option value="1" {{ $val && $val->value == '1' ? 'selected' : '' }}>হ্যাঁ</option>
                                    <option value="0" {{ $val && $val->value == '0' ? 'selected' : '' }}>না</option>
                                </select>
                            @elseif($attr->type === 'date')
                                <input type="date" name="attribute[{{ $attr->id }}]" value="{{ old('attribute.'.$attr->id, $val->value ?? '') }}" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                            @elseif($attr->type === 'number')
                                <input type="number" name="attribute[{{ $attr->id }}]" value="{{ old('attribute.'.$attr->id, $val->value ?? '') }}" step="any" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                            @else
                                <input type="text" name="attribute[{{ $attr->id }}]" value="{{ old('attribute.'.$attr->id, $val->value ?? '') }}" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                            @endif
                        </div>
                    @endforeach
                </div>
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
                            <tr id="variant-row-{{ $variant->id }}">
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
                                    <p class="text-xs text-gray-500">প্রথমে ক্যাটাগরি সিলেক্ট করুন, অ্যাট্রিবিউট দেখা যাবে</p>
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

            {{-- Existing Images --}}
            @if($product->images->count())
            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900 mb-4">বিদ্যমান ইমেজ</h2>
                <div class="grid grid-cols-4 gap-4">
                    @foreach($product->images as $image)
                    <div class="relative group">
                        <img src="{{ asset('storage/' . $image->image_path) }}" class="w-full h-24 object-cover rounded-lg">
                        <label class="absolute top-1 right-1">
                            <input type="checkbox" name="delete_images[]" value="{{ $image->id }}" class="hidden peer">
                            <span class="bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center cursor-pointer opacity-0 group-hover:opacity-100 transition peer-checked:opacity-100">✕</span>
                        </label>
                        @if($image->hasAnalysis())
                            <div class="absolute bottom-1 left-1 bg-green-500 text-white text-xs px-1 py-0.5 rounded">AI ✓</div>
                        @endif
                    </div>
                    @endforeach
                </div>
                <p class="text-sm text-gray-500 mt-2">ডিলিট করতে চাইলে ইমেজের উপর ক্লিক করুন</p>
            </div>
            @endif

            {{-- New Images --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900 mb-4">নতুন ইমেজ যোগ করুন</h2>
                <div id="image-dropzone" class="relative border-2 border-dashed border-gray-300 rounded-xl p-8 text-center cursor-pointer hover:border-purple-400 hover:bg-purple-50 transition-all duration-200">
                    <input type="file" name="images[]" multiple accept="image/*" class="hidden" id="image-input">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="mt-2 text-sm text-gray-600">ড্র্যাগ করুন অথবা <span class="text-purple-600 font-medium">ক্লিক করে সিলেক্ট করুন</span></p>
                    <p class="mt-1 text-xs text-gray-400">PNG, JPG, WEBP (সর্বোচ্চ ৫MB প্রতিটি)</p>
                </div>
                <p class="text-sm text-gray-500 mt-1">নতুন ইমেজ আপলোড করলে AI অটোমেটিক্যালি বিশ্লেষণ করবে।</p>
                <div id="image-preview" class="grid grid-cols-4 gap-4 mt-4"></div>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ route('inventory.products.index') }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition">বাতিল</a>
                <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition">আপডেট করুন</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// Category change handler for attributes
document.getElementById('category_id').addEventListener('change', function() {
    const categoryId = this.value;
    fetch('{{ route("inventory.products.attributes") }}?category_id=' + categoryId)
        .then(r => r.json())
        .then(attributes => {
            const section = document.getElementById('attributes-section');
            const container = document.getElementById('attributes-container');
            if (!attributes.length) { section.style.display = 'none'; return; }
            section.style.display = 'block';
            container.innerHTML = attributes.map(attr => {
                let input = '';
                if (attr.type === 'select') {
                    input = `<select name="attribute[${attr.id}]" class="w-full border border-gray-300 rounded-xl px-4 py-2"><option value="">নির্বাচন করুন</option>${(attr.options||[]).map(o=>`<option value="${o}">${o}</option>`).join('')}</select>`;
                } else if (attr.type === 'boolean') {
                    input = `<select name="attribute[${attr.id}]" class="w-full border border-gray-300 rounded-xl px-4 py-2"><option value="">নির্বাচন</option><option value="1">হ্যাঁ</option><option value="0">না</option></select>`;
                } else if (attr.type === 'date') {
                    input = `<input type="date" name="attribute[${attr.id}]" class="w-full border border-gray-300 rounded-xl px-4 py-2">`;
                } else if (attr.type === 'number') {
                    input = `<input type="number" name="attribute[${attr.id}]" step="any" class="w-full border border-gray-300 rounded-xl px-4 py-2">`;
                } else {
                    input = `<input type="text" name="attribute[${attr.id}]" class="w-full border border-gray-300 rounded-xl px-4 py-2">`;
                }
                return `<div><label class="block text-sm font-medium text-gray-700 mb-1">${attr.name}</label>${input}</div>`;
            }).join('');
        });
});

// Variant Modal
const variantModal = document.getElementById('variant-modal');
const variantForm = document.getElementById('variant-form');
const variantMethodField = document.getElementById('variant-method-field');
const variantModalTitle = document.getElementById('variant-modal-title');
const variantSubmitBtn = document.getElementById('variant-submit-btn');
const variantAttributesFields = document.getElementById('variant-attributes-fields');

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
    variantForm.action = '{{ url("/dashboard/products") }}/' + '{{ $product->id }}' + '/variants/' + variant.id;

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
    const categoryId = document.getElementById('category_id').value;
    if (!categoryId) {
        variantAttributesFields.innerHTML = '<p class="text-xs text-gray-500">প্রথমে ক্যাটাগরি সিলেক্ট করুন</p>';
        return;
    }

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

// Image dropzone
const imageDropzone = document.getElementById('image-dropzone');
const imageInput = document.getElementById('image-input');
const imagePreview = document.getElementById('image-preview');
let selectedFiles = [];

imageDropzone.addEventListener('click', () => imageInput.click());

imageDropzone.addEventListener('dragover', (e) => {
    e.preventDefault();
    imageDropzone.classList.add('border-purple-500', 'bg-purple-50');
});

imageDropzone.addEventListener('dragleave', () => {
    imageDropzone.classList.remove('border-purple-500', 'bg-purple-50');
});

imageDropzone.addEventListener('drop', (e) => {
    e.preventDefault();
    imageDropzone.classList.remove('border-purple-500', 'bg-purple-50');
    const files = Array.from(e.target.files || e.dataTransfer.files).filter(f => f.type.startsWith('image/'));
    addFiles(files);
});

imageInput.addEventListener('change', (e) => {
    addFiles(Array.from(e.target.files));
});

function addFiles(files) {
    files.forEach(file => {
        if (!selectedFiles.find(f => f.name === file.name && f.size === file.size)) {
            selectedFiles.push(file);
        }
    });
    updatePreview();
    updateInputFiles();
}

function removeFile(index) {
    selectedFiles.splice(index, 1);
    updatePreview();
    updateInputFiles();
}

function updateInputFiles() {
    const dt = new DataTransfer();
    selectedFiles.forEach(f => dt.items.add(f));
    imageInput.files = dt.files;
}

function updatePreview() {
    imagePreview.innerHTML = '';
    selectedFiles.forEach((file, i) => {
        const reader = new FileReader();
        reader.onload = function(ev) {
            const div = document.createElement('div');
            div.className = 'relative group';
            div.innerHTML = `
                <img src="${ev.target.result}" class="w-full h-24 object-cover rounded-lg">
                <button type="button" onclick="removeFile(${i})" class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition">✕</button>
                <div class="absolute bottom-1 left-1 bg-blue-500 text-white text-xs px-1.5 py-0.5 rounded">AI বিশ্লেষণ</div>`;
            imagePreview.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}
</script>
@endpush
@endsection
