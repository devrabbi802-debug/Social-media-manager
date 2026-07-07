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

            {{-- Basic Info --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <div class="flex items-center space-x-3 mb-4">
                    <span class="w-8 h-8 bg-purple-600 text-white rounded-full flex items-center justify-center text-sm font-bold">1</span>
                    <h2 class="text-lg font-bold text-gray-900">মৌলিক তথ্য</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">প্রোডাক্টের নাম *</label>
                        <input type="text" name="name" value="{{ old('name', $product->name) }}" required class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">SKU *</label>
                        <input type="text" name="sku" id="product-sku" value="{{ old('sku', $product->sku) }}" required class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
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
                                @foreach($cat->children as $child)
                                    <option value="{{ $child->id }}" {{ old('category_id', $product->category_id) == $child->id ? 'selected' : '' }}>&nbsp;&nbsp;&nbsp;└ {{ $child->name }}</option>
                                    @foreach($child->children as $grandchild)
                                        <option value="{{ $grandchild->id }}" {{ old('category_id', $product->category_id) == $grandchild->id ? 'selected' : '' }}>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;└ {{ $grandchild->name }}</option>
                                    @endforeach
                                @endforeach
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

            {{-- Pricing --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <div class="flex items-center space-x-3 mb-4">
                    <span class="w-8 h-8 bg-purple-600 text-white rounded-full flex items-center justify-center text-sm font-bold">2</span>
                    <h2 class="text-lg font-bold text-gray-900">মূল্য</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">মূল মূল্য (৳) *</label>
                        <input type="number" name="base_price" id="base-price" value="{{ old('base_price', $product->base_price) }}" step="0.01" min="0" required class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                        @error('base_price') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ডিসকাউন্ট মূল্য (৳)</label>
                        <input type="number" name="discount_price" value="{{ old('discount_price', $product->discount_price) }}" step="0.01" min="0" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
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

            {{-- Existing Options --}}
            @php
                $existingOptions = $attributeTemplates->where('is_variant_option', true);
                $existingAttributes = $attributeTemplates->where('is_variant_option', false);
            @endphp

            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <div class="flex items-center space-x-3 mb-2">
                    <span class="w-8 h-8 bg-purple-600 text-white rounded-full flex items-center justify-center text-sm font-bold">3</span>
                    <h2 class="text-lg font-bold text-gray-900">অপশন (ভ্যারিয়েন্ট)</h2>
                </div>
                <p class="text-sm text-gray-500 mb-4 ml-11">নতুন অপশন যোগ করলে নতুন ভ্যারিয়েন্ট জেনারেট হবে। বিদ্যমান ভ্যারিয়েন্ট ডিলিট হবে না।</p>

                <div id="options-container" class="space-y-4">
                    @foreach($existingOptions as $opt)
                        <div class="option-group border border-gray-200 rounded-xl p-4 bg-gray-50" id="option-existing-{{ $opt->id }}">
                            <div class="flex items-center justify-between mb-3">
                                <span class="font-medium text-gray-700">অপশন: {{ $opt->name }}</span>
                                <span class="text-xs text-gray-400">ID: {{ $opt->id }}</span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">অপশনের নাম *</label>
                                    <input type="text" name="options[existing_{{ $opt->id }}][name]" value="{{ $opt->name }}" required
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500"
                                        onchange="generateMatrix()">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">মানগুলো (কমা দিয়ে আলাদা করুন) *</label>
                                    <input type="text" name="options[existing_{{ $opt->id }}][values]" value="{{ implode(', ', $opt->options ?? []) }}" required
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500"
                                        placeholder="যেমন: Red, Blue, Green"
                                        onchange="generateMatrix()">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <button type="button" onclick="addOption()" class="mt-4 px-4 py-2 border-2 border-dashed border-gray-300 rounded-xl text-gray-600 font-medium hover:border-purple-400 hover:text-purple-600 hover:bg-purple-50 transition w-full">
                    + নতুন অপশন যোগ করুন
                </button>
            </div>

            {{-- Existing Variants --}}
            @if($product->variants->count())
            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <div class="flex items-center space-x-3 mb-4">
                    <span class="w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center text-sm font-bold">✓</span>
                    <h2 class="text-lg font-bold text-gray-900">বিদ্যমান ভ্যারিয়েন্ট ({{ $product->variants->count() }})</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm border">
                        <thead>
                            <tr class="bg-gray-100">
                                @foreach($existingOptions as $opt)
                                    <th class="px-3 py-2 font-medium text-left border">{{ $opt->name }}</th>
                                @endforeach
                                <th class="px-3 py-2 font-medium text-left border">SKU</th>
                                <th class="px-3 py-2 font-medium text-left border">মূল্য</th>
                                <th class="px-3 py-2 font-medium text-left border">স্টক</th>
                                <th class="px-3 py-2 font-medium text-left border">অ্যাকশন</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($product->variants as $variant)
                            <tr class="hover:bg-gray-50">
                                @foreach($existingOptions as $opt)
                                    <td class="px-3 py-2 border">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700">
                                            {{ $variant->attributes[$opt->name] ?? '-' }}
                                        </span>
                                    </td>
                                @endforeach
                                <td class="px-3 py-2 border font-mono text-xs">{{ $variant->sku }}</td>
                                <td class="px-3 py-2 border">৳{{ number_format($variant->effective_price, 2) }}</td>
                                <td class="px-3 py-2 border">{{ $variant->stock_quantity }}</td>
                                <td class="px-3 py-2 border">
                                    <div class="flex items-center space-x-2">
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
            </div>
            @endif

            {{-- New Variants Matrix --}}
            <div id="variant-matrix-section" class="hidden bg-white rounded-2xl p-6 shadow-sm">
                <div class="flex items-center space-x-3 mb-4">
                    <span class="w-8 h-8 bg-purple-600 text-white rounded-full flex items-center justify-center text-sm font-bold">4</span>
                    <h2 class="text-lg font-bold text-gray-900">নতুন ভ্যারিয়েন্ট ম্যাট্রিক্স</h2>
                </div>
                <div id="matrix-info" class="mb-4 p-3 bg-purple-50 rounded-xl">
                    <p class="text-sm text-purple-700 font-medium" id="matrix-count"></p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm border" id="variant-matrix">
                        <thead id="matrix-header"></thead>
                        <tbody id="matrix-body"></tbody>
                    </table>
                </div>
                <p class="text-xs text-gray-500 mt-3">* SKU অটো জেনারেট হয়েছে। প্রয়োজনে পরিবর্তন করতে পারেন।</p>
            </div>

            {{-- Product-level Attributes --}}
            @if($existingAttributes->count())
            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <div class="flex items-center space-x-3 mb-4">
                    <span class="w-8 h-8 bg-gray-400 text-white rounded-full flex items-center justify-center text-sm font-bold">+</span>
                    <h2 class="text-lg font-bold text-gray-900">অতিরিক্ত অ্যাট্রিবিউট</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($existingAttributes as $attr)
                        @php $val = $product->attributeValues->where('attribute_template_id', $attr->id)->first(); @endphp
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ $attr->name }}</label>
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
            @endif

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
                <div class="flex items-center space-x-3 mb-4">
                    <span class="w-8 h-8 bg-purple-600 text-white rounded-full flex items-center justify-center text-sm font-bold">5</span>
                    <h2 class="text-lg font-bold text-gray-900">নতুন ইমেজ যোগ করুন</h2>
                </div>
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
let optionIndex = 100;
const existingOptions = @json($existingOptions->map(fn($o) => ['name' => $o->name, 'values' => $o->options ?? []])->values());

function addOption(name = '', values = []) {
    optionIndex++;
    const idx = optionIndex;
    const container = document.getElementById('options-container');

    const div = document.createElement('div');
    div.className = 'option-group border border-gray-200 rounded-xl p-4 bg-gray-50';
    div.id = 'option-' + idx;
    div.innerHTML = `
        <div class="flex items-center justify-between mb-3">
            <span class="font-medium text-gray-700">নতুন অপশন</span>
            <button type="button" onclick="removeOption(${idx})" class="text-red-500 hover:text-red-700 text-sm font-medium">✕ মুছুন</button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">অপশনের নাম *</label>
                <input type="text" name="options[${idx}][name]" value="${name}" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500"
                    placeholder="যেমন: Color, Size"
                    onchange="generateMatrix()">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">মানগুলো (কমা দিয়ে আলাদা করুন) *</label>
                <input type="text" name="options[${idx}][values]" value="${values.join(', ')}" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500"
                    placeholder="যেমন: Red, Blue, Green"
                    onchange="generateMatrix()">
            </div>
        </div>
    `;
    container.appendChild(div);
}

function removeOption(idx) {
    const div = document.getElementById('option-' + idx) || document.getElementById('option-existing-' + idx);
    if (div) div.remove();
    generateMatrix();
}

function generateMatrix() {
    const container = document.getElementById('options-container');
    const optionGroups = container.querySelectorAll('.option-group');
    const matrixSection = document.getElementById('variant-matrix-section');
    const matrixHeader = document.getElementById('matrix-header');
    const matrixBody = document.getElementById('matrix-body');
    const matrixCount = document.getElementById('matrix-count');
    const baseSku = document.getElementById('product-sku').value || 'PRODUCT';
    const basePrice = document.getElementById('base-price').value || '0';

    if (optionGroups.length === 0) {
        matrixSection.classList.add('hidden');
        return;
    }

    const options = [];
    optionGroups.forEach(group => {
        const nameInput = group.querySelector('input[name$="[name]"]');
        const valuesInput = group.querySelector('input[name$="[values]"]');
        const name = nameInput.value.trim();
        const values = valuesInput.value.split(',').map(v => v.trim()).filter(v => v);

        if (name && values.length > 0) {
            options.push({ name, values });
        }
    });

    if (options.length === 0) {
        matrixSection.classList.add('hidden');
        return;
    }

    const combinations = getCombinations(options);
    matrixCount.textContent = `${combinations.length}টি নতুন ভ্যারিয়েন্ট জেনারেট হবে`;

    let headerHtml = '<tr class="bg-gray-100">';
    options.forEach(opt => {
        headerHtml += `<th class="px-3 py-2 font-medium text-left border">${opt.name}</th>`;
    });
    headerHtml += '<th class="px-3 py-2 font-medium text-left border">SKU</th>';
    headerHtml += '<th class="px-3 py-2 font-medium text-left border">মূল্য (৳)</th>';
    headerHtml += '<th class="px-3 py-2 font-medium text-left border">স্টক *</th>';
    headerHtml += '<th class="px-3 py-2 font-medium text-left border">বারকোড</th>';
    headerHtml += '</tr>';
    matrixHeader.innerHTML = headerHtml;

    let bodyHtml = '';
    combinations.forEach((combo, i) => {
        const skuParts = [baseSku];
        combo.forEach(val => skuParts.push(val.toUpperCase().replace(/\s+/g, '-')));
        const autoSku = skuParts.join('-');

        bodyHtml += `<tr class="hover:bg-gray-50">`;
        combo.forEach((val, j) => {
            bodyHtml += `<td class="px-3 py-2 border"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700">${val}</span><input type="hidden" name="variants[${i}][attributes][${options[j].name}]" value="${val}"></td>`;
        });
        bodyHtml += `<td class="px-3 py-2 border"><input type="text" name="variants[${i}][sku]" value="${autoSku}" required class="w-full border border-gray-200 rounded px-2 py-1 text-xs font-mono focus:ring-1 focus:ring-purple-500"></td>`;
        bodyHtml += `<td class="px-3 py-2 border"><input type="number" name="variants[${i}][price]" value="${basePrice}" step="0.01" min="0" class="w-full border border-gray-200 rounded px-2 py-1 text-xs focus:ring-1 focus:ring-purple-500"></td>`;
        bodyHtml += `<td class="px-3 py-2 border"><input type="number" name="variants[${i}][stock_quantity]" value="0" min="0" required class="w-full border border-gray-200 rounded px-2 py-1 text-xs focus:ring-1 focus:ring-purple-500"></td>`;
        bodyHtml += `<td class="px-3 py-2 border"><input type="text" name="variants[${i}][barcode]" class="w-full border border-gray-200 rounded px-2 py-1 text-xs focus:ring-1 focus:ring-purple-500"></td>`;
        bodyHtml += '</tr>';
    });
    matrixBody.innerHTML = bodyHtml;
    matrixSection.classList.remove('hidden');
}

function getCombinations(options) {
    if (options.length === 0) return [[]];
    const result = [];
    const first = options[0];
    const rest = getCombinations(options.slice(1));
    first.values.forEach(val => {
        rest.forEach(combo => result.push([val, ...combo]));
    });
    return result;
}

// Image dropzone
const imageDropzone = document.getElementById('image-dropzone');
const imageInput = document.getElementById('image-input');
const imagePreview = document.getElementById('image-preview');
let selectedFiles = [];

imageDropzone.addEventListener('click', () => imageInput.click());
imageDropzone.addEventListener('dragover', (e) => { e.preventDefault(); imageDropzone.classList.add('border-purple-500', 'bg-purple-50'); });
imageDropzone.addEventListener('dragleave', () => imageDropzone.classList.remove('border-purple-500', 'bg-purple-50'));
imageDropzone.addEventListener('drop', (e) => { e.preventDefault(); imageDropzone.classList.remove('border-purple-500', 'bg-purple-50'); addFiles(Array.from(e.target.files || e.dataTransfer.files).filter(f => f.type.startsWith('image/'))); });
imageInput.addEventListener('change', (e) => addFiles(Array.from(e.target.files)));

function addFiles(files) {
    files.forEach(file => { if (!selectedFiles.find(f => f.name === file.name && f.size === file.size)) selectedFiles.push(file); });
    updatePreview();
    updateInputFiles();
}
function removeFile(index) { selectedFiles.splice(index, 1); updatePreview(); updateInputFiles(); }
function updateInputFiles() { const dt = new DataTransfer(); selectedFiles.forEach(f => dt.items.add(f)); imageInput.files = dt.files; }
function updatePreview() {
    imagePreview.innerHTML = '';
    selectedFiles.forEach((file, i) => {
        const reader = new FileReader();
        reader.onload = function(ev) {
            const div = document.createElement('div');
            div.className = 'relative group';
            div.innerHTML = `<img src="${ev.target.result}" class="w-full h-24 object-cover rounded-lg"><button type="button" onclick="removeFile(${i})" class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition">✕</button>`;
            imagePreview.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}

document.getElementById('product-sku').addEventListener('input', generateMatrix);
</script>
@endpush
@endsection
