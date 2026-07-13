@extends('layouts.tenant')

@section('title', 'নতুন প্রোডাক্ট যোগ - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">নতুন প্রোডাক্ট যোগ করুন</h1>
                    <p class="text-gray-600">প্রোডাক্টের তথ্য ও ভ্যারিয়েন্ট পূরণ করুন</p>
                </div>
                <a href="{{ route('inventory.products.index') }}" class="text-gray-600 hover:text-purple-600 font-medium">← ফিরে যান</a>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form action="{{ route('inventory.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            {{-- Step 1: Basic Info --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <div class="flex items-center space-x-3 mb-4">
                    <span class="w-8 h-8 bg-purple-600 text-white rounded-full flex items-center justify-center text-sm font-bold">1</span>
                    <h2 class="text-lg font-bold text-gray-900">মৌলিক তথ্য</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">প্রোডাক্টের নাম *</label>
                        <input type="text" name="name" id="product-name" value="{{ old('name') }}" required
                            class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            placeholder="যেমন: ক্লাসিক টি-শার্ট">
                        @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">SKU (প্রোডাক্ট কোড) *</label>
                        <input type="text" name="sku" id="product-sku" value="{{ old('sku') }}" required
                            class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500"
                            placeholder="যেমন: TSHIRT-001">
                        <p class="text-xs text-gray-500 mt-1">ভ্যারিয়েন্ট SKU এই থেকে অটো জেনারেট হবে</p>
                        @error('sku') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">বারকোড</label>
                        <input type="text" name="barcode" value="{{ old('barcode') }}"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ক্যাটাগরি *</label>
                        <select name="category_id" id="category_id" required class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                            <option value="">ক্যাটাগরি নির্বাচন করুন</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @foreach($cat->children as $child)
                                    <option value="{{ $child->id }}" {{ old('category_id') == $child->id ? 'selected' : '' }}>&nbsp;&nbsp;&nbsp;└ {{ $child->name }}</option>
                                    @foreach($child->children as $grandchild)
                                        <option value="{{ $grandchild->id }}" {{ old('category_id') == $grandchild->id ? 'selected' : '' }}>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;└ {{ $grandchild->name }}</option>
                                    @endforeach
                                @endforeach
                            @endforeach
                        </select>
                        @error('category_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ব্র্যান্ড</label>
                        <select name="brand_id" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                            <option value="">ব্র্যান্ড নির্বাচন করুন</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">বিবরণ</label>
                        <textarea name="description" rows="3"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            placeholder="প্রোডাক্টের বিস্তারিত বিবরণ লিখুন">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Step 2: Pricing --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <div class="flex items-center space-x-3 mb-4">
                    <span class="w-8 h-8 bg-purple-600 text-white rounded-full flex items-center justify-center text-sm font-bold">2</span>
                    <h2 class="text-lg font-bold text-gray-900">মূল্য</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">মূল মূল্য (৳) *</label>
                        <input type="number" name="base_price" id="base-price" value="{{ old('base_price') }}" step="0.01" min="0" required
                            class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                        @error('base_price') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ডিসকাউন্ট মূল্য (৳)</label>
                        <input type="number" name="discount_price" value="{{ old('discount_price') }}" step="0.01" min="0"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">একক *</label>
                        <select name="unit" required class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                            <option value="pcs" {{ old('unit') === 'pcs' ? 'selected' : '' }}>পিস (pcs)</option>
                            <option value="kg" {{ old('unit') === 'kg' ? 'selected' : '' }}>কেজি (kg)</option>
                            <option value="liter" {{ old('unit') === 'liter' ? 'selected' : '' }}>লিটার (liter)</option>
                            <option value="pack" {{ old('unit') === 'pack' ? 'selected' : '' }}>প্যাক (pack)</option>
                            <option value="box" {{ old('unit') === 'box' ? 'selected' : '' }}>বক্স (box)</option>
                            <option value="pair" {{ old('unit') === 'pair' ? 'selected' : '' }}>জোড়া (pair)</option>
                            <option value="set" {{ old('unit') === 'set' ? 'selected' : '' }}>সেট (set)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">স্ট্যাটাস</label>
                        <select name="status" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                            <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>সক্রিয়</option>
                            <option value="inactive" {{ old('status', 'inactive') === 'inactive' ? 'selected' : '' }}>নিষ্ক্রিয়</option>
                            <option value="out_of_stock" {{ old('status', 'out_of_stock') === 'out_of_stock' ? 'selected' : '' }}>স্টক শেষ</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ফিচার্ড</label>
                        <select name="is_featured" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                            <option value="0" {{ old('is_featured') == '0' ? 'selected' : '' }}>না</option>
                            <option value="1" {{ old('is_featured') == '1' ? 'selected' : '' }}>হ্যাঁ</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Step 3: Options (Shopify Style) --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <div class="flex items-center space-x-3 mb-2">
                    <span class="w-8 h-8 bg-purple-600 text-white rounded-full flex items-center justify-center text-sm font-bold">3</span>
                    <h2 class="text-lg font-bold text-gray-900">অপশন (ভ্যারিয়েন্ট তৈরি করতে)</h2>
                </div>
                <p class="text-sm text-gray-500 mb-4 ml-11">যদি প্রোডাক্টে Color, Size ইত্যাদি ভ্যারিয়েন্ট থাকে, তাহলে এখানে অপশন যোগ করুন। সিস্টেম অটোমেটিক্যালি সব কম্বিনেশন তৈরি করবে।</p>

                {{-- Existing options dropdown (category select korle dibe) --}}
                <div id="existing-options-section" class="hidden mb-4 p-4 bg-blue-50 rounded-xl border border-blue-200">
                    <label class="block text-sm font-medium text-blue-800 mb-2">বিদ্যমান অপশন থেকে নির্বাচন করুন:</label>
                    <div class="flex flex-wrap gap-2" id="existing-options-list">
                        {{-- Dynamically filled --}}
                    </div>
                    <p class="text-xs text-blue-600 mt-2">অথবা নিচে নতুন অপশন যোগ করুন</p>
                </div>

                <div id="options-container" class="space-y-4">
                    {{-- Options will be added here dynamically --}}
                </div>

                <button type="button" onclick="addOption()" class="mt-4 px-4 py-2 border-2 border-dashed border-gray-300 rounded-xl text-gray-600 font-medium hover:border-purple-400 hover:text-purple-600 hover:bg-purple-50 transition w-full">
                    + অপশন যোগ করুন (যেমন: Color, Size, Material)
                </button>

                <div id="no-option-msg" class="text-center py-8 text-gray-500">
                    <p>কোনো অপশন নেই। অপশন যোগ করলে সিস্টেম অটোমেটিক্যালি ভ্যারিয়েন্ট তৈরি করবে।</p>
                    <p class="text-xs mt-1">সিঙ্গল প্রোডাক্ট হলে অপশন ছাড়াই সেভ করতে পারেন।</p>
                </div>
            </div>

            {{-- Step 4: Variant Matrix --}}
            <div id="variant-matrix-section" class="hidden bg-white rounded-2xl p-6 shadow-sm">
                <div class="flex items-center space-x-3 mb-4">
                    <span class="w-8 h-8 bg-purple-600 text-white rounded-full flex items-center justify-center text-sm font-bold">4</span>
                    <h2 class="text-lg font-bold text-gray-900">ভ্যারিয়েন্ট ম্যাট্রিক্স</h2>
                </div>

                <div id="matrix-info" class="mb-4 p-3 bg-purple-50 rounded-xl">
                    <p class="text-sm text-purple-700 font-medium" id="matrix-count"></p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm border" id="variant-matrix">
                        <thead id="matrix-header">
                        </thead>
                        <tbody id="matrix-body">
                        </tbody>
                    </table>
                </div>

                <p class="text-xs text-gray-500 mt-3">* SKU অটো জেনারেট হয়েছে। প্রয়োজনে পরিবর্তন করতে পারেন।</p>
            </div>

            {{-- Step 5: Images --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <div class="flex items-center space-x-3 mb-4">
                    <span class="w-8 h-8 bg-purple-600 text-white rounded-full flex items-center justify-center text-sm font-bold">5</span>
                    <h2 class="text-lg font-bold text-gray-900">প্রোডাক্ট ইমেজ</h2>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ইমেজ (একাধিক আপলোড করতে পারেন)</label>
                    <div id="image-dropzone" class="relative border-2 border-dashed border-gray-300 rounded-xl p-8 text-center cursor-pointer hover:border-purple-400 hover:bg-purple-50 transition-all duration-200">
                        <input type="file" name="images[]" multiple accept="image/*" class="hidden" id="image-input">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="mt-2 text-sm text-gray-600">ড্র্যাগ করুন অথবা <span class="text-purple-600 font-medium">ক্লিক করে সিলেক্ট করুন</span></p>
                        <p class="mt-1 text-xs text-gray-400">PNG, JPG, WEBP (সর্বোচ্চ ৫MB প্রতিটি)</p>
                    </div>
                    <p class="text-sm text-gray-500 mt-1">AI অটোমেটিক্যালি ইমেজ বিশ্লেষণ করবে।</p>
                </div>
                <div id="image-preview" class="grid grid-cols-4 gap-4 mt-4"></div>
            </div>

            {{-- Step 6: SEO --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <div class="flex items-center space-x-3 mb-4">
                    <span class="w-8 h-8 bg-purple-600 text-white rounded-full flex items-center justify-center text-sm font-bold">6</span>
                    <h2 class="text-lg font-bold text-gray-900">SEO তথ্য</h2>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">মেটা টাইটেল</label>
                        <input type="text" name="meta_title" value="{{ old('meta_title') }}"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500"
                            placeholder="সার্চ ইঞ্জিনে যে টাইটেল দেখাবে">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">মেটা বিবরণ</label>
                        <textarea name="meta_description" rows="2"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500"
                            placeholder="সার্চ ইঞ্জিনে যে বিবরণ দেখাবে">{{ old('meta_description') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ route('inventory.products.index') }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition">বাতিল</a>
                <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition">প্রোডাক্ট সংরক্ষণ করুন</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
let options = [];
let optionIndex = 0;
let existingVariantOptions = [];

// Category select hole existing variant options fetch korbo
document.getElementById('category_id').addEventListener('change', function() {
    const categoryId = this.value;
    const section = document.getElementById('existing-options-section');
    const list = document.getElementById('existing-options-list');

    if (!categoryId) {
        section.classList.add('hidden');
        return;
    }

    fetch(`{{ route('inventory.products.variant-options') }}?category_id=${categoryId}`)
        .then(res => res.json())
        .then(data => {
            existingVariantOptions = data;
            if (data.length === 0) {
                section.classList.add('hidden');
                return;
            }

            list.innerHTML = '';
            data.forEach(opt => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'px-3 py-1.5 bg-white border border-blue-300 rounded-lg text-sm text-blue-700 hover:bg-blue-100 hover:border-blue-400 transition font-medium';
                btn.textContent = `${opt.name} (${Array.isArray(opt.options) ? opt.options.length : 0} values)`;
                btn.onclick = () => addExistingOption(opt);
                list.appendChild(btn);
            });

            section.classList.remove('hidden');
        });
});

// Existing option click hole auto-fill
function addExistingOption(opt) {
    const values = Array.isArray(opt.options) ? opt.options : [];
    addOption(opt.name, values);
}

// Page load e default category hole fetch koro
document.addEventListener('DOMContentLoaded', function() {
    const cat = document.getElementById('category_id');
    if (cat.value) cat.dispatchEvent(new Event('change'));
});

function addOption(name = '', values = []) {
    optionIndex++;
    const idx = optionIndex;
    const container = document.getElementById('options-container');
    const noMsg = document.getElementById('no-option-msg');
    noMsg.style.display = 'none';

    const div = document.createElement('div');
    div.className = 'option-group border border-gray-200 rounded-xl p-4 bg-gray-50';
    div.id = 'option-' + idx;
    div.innerHTML = `
        <div class="flex items-center justify-between mb-3">
            <span class="font-medium text-gray-700">অপশন #${idx}</span>
            <button type="button" onclick="removeOption(${idx})" class="text-red-500 hover:text-red-700 text-sm font-medium">✕ মুছুন</button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">অপশনের নাম *</label>
                <input type="text" name="options[${idx}][name]" value="${name}" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500"
                    placeholder="যেমন: Color, Size, Material"
                    onchange="generateMatrix()">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">মানগুলো (কমা দিয়ে আলাদা করুন) *</label>
                <input type="text" name="options[${idx}][values]" value="${values.join(', ')}" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500"
                    placeholder="যেমন: Red, Blue, Green"
                    onchange="generateMatrix()">
                <p class="text-xs text-gray-400 mt-1">কমা দিয়ে আলাদা করুন: Red, Blue, Green</p>
            </div>
        </div>
    `;
    container.appendChild(div);
    generateMatrix();
}

function removeOption(idx) {
    const div = document.getElementById('option-' + idx);
    if (div) div.remove();
    generateMatrix();

    const container = document.getElementById('options-container');
    const noMsg = document.getElementById('no-option-msg');
    if (container.children.length === 0) {
        noMsg.style.display = 'block';
    }
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

    options = [];
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
    matrixCount.textContent = `${combinations.length}টি ভ্যারিয়েন্ট জেনারেট হয়েছে`;

    // Header
    let headerHtml = '<tr class="bg-gray-100">';
    options.forEach(opt => {
        headerHtml += `<th class="px-3 py-2 font-medium text-left border">${opt.name}</th>`;
    });
    headerHtml += '<th class="px-3 py-2 font-medium text-left border">ইমেজ</th>';
    headerHtml += '<th class="px-3 py-2 font-medium text-left border">SKU</th>';
    headerHtml += '<th class="px-3 py-2 font-medium text-left border">মূল্য (৳)</th>';
    headerHtml += '<th class="px-3 py-2 font-medium text-left border">স্টক *</th>';
    headerHtml += '<th class="px-3 py-2 font-medium text-left border">বারকোড</th>';
    headerHtml += '</tr>';
    matrixHeader.innerHTML = headerHtml;

    // Body
    let bodyHtml = '';
    combinations.forEach((combo, i) => {
        const skuParts = [baseSku];
        combo.forEach(val => {
            skuParts.push(val.toUpperCase().replace(/\s+/g, '-'));
        });
        const autoSku = skuParts.join('-');

        bodyHtml += `<tr class="hover:bg-gray-50 ${i % 2 === 0 ? 'bg-white' : 'bg-gray-50/50'}">`;
        combo.forEach((val, j) => {
            bodyHtml += `<td class="px-3 py-2 border">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700">${val}</span>
                <input type="hidden" name="variants[${i}][attributes][${options[j].name}]" value="${val}">
            </td>`;
        });
        bodyHtml += `<td class="px-3 py-2 border"><label class="inline-flex items-center px-2 py-1 bg-gray-100 rounded-lg text-xs text-gray-600 hover:bg-gray-200 cursor-pointer transition"><svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>ইমেজ<input type="file" name="variants[${i}][images][]" multiple accept="image/*" class="hidden" onchange="previewMatrixImage(this, ${i})"></label><div id="matrix-img-preview-${i}" class="flex flex-wrap gap-1 mt-1"></div></td>`;
        bodyHtml += `<td class="px-3 py-2 border"><input type="text" name="variants[${i}][sku]" value="${autoSku}" required class="w-full border border-gray-200 rounded px-2 py-1 text-xs font-mono focus:ring-1 focus:ring-purple-500"></td>`;
        bodyHtml += `<td class="px-3 py-2 border"><input type="number" name="variants[${i}][price]" value="${basePrice}" step="0.01" min="0" class="w-full border border-gray-200 rounded px-2 py-1 text-xs focus:ring-1 focus:ring-purple-500"></td>`;
        bodyHtml += `<td class="px-3 py-2 border"><input type="number" name="variants[${i}][stock_quantity]" value="0" min="0" required class="w-full border border-gray-200 rounded px-2 py-1 text-xs focus:ring-1 focus:ring-purple-500"></td>`;
        bodyHtml += `<td class="px-3 py-2 border"><input type="text" name="variants[${i}][barcode]" class="w-full border border-gray-200 rounded px-2 py-1 text-xs focus:ring-1 focus:ring-purple-500"></td>`;
        bodyHtml += '</tr>';
    });
    matrixBody.innerHTML = bodyHtml;
    matrixSection.classList.remove('hidden');
}

function previewMatrixImage(input, variantIndex) {
    const container = document.getElementById('matrix-img-preview-' + variantIndex);
    container.innerHTML = '';
    const files = input.files;

    for (let i = 0; i < files.length; i++) {
        const reader = new FileReader();
        reader.onload = function(ev) {
            const img = document.createElement('img');
            img.src = ev.target.result;
            img.className = 'w-8 h-8 object-cover rounded border border-green-400';
            container.appendChild(img);
        };
        reader.readAsDataURL(files[i]);
    }
}

function getCombinations(options) {
    if (options.length === 0) return [[]];
    const result = [];
    const first = options[0];
    const rest = getCombinations(options.slice(1));
    first.values.forEach(val => {
        rest.forEach(combo => {
            result.push([val, ...combo]);
        });
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
imageDropzone.addEventListener('dragleave', () => { imageDropzone.classList.remove('border-purple-500', 'bg-purple-50'); });
imageDropzone.addEventListener('drop', (e) => { e.preventDefault(); imageDropzone.classList.remove('border-purple-500', 'bg-purple-50'); addFiles(Array.from(e.target.files || e.dataTransfer.files).filter(f => f.type.startsWith('image/'))); });
imageInput.addEventListener('change', (e) => { addFiles(Array.from(e.target.files)); });

function addFiles(files) {
    files.forEach(file => { if (!selectedFiles.find(f => f.name === file.name && f.size === file.size)) { selectedFiles.push(file); } });
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
            div.innerHTML = `<img src="${ev.target.result}" class="w-full h-24 object-cover rounded-lg"><button type="button" onclick="removeFile(${i})" class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition">✕</button><div class="absolute bottom-1 left-1 bg-blue-500 text-white text-xs px-1.5 py-0.5 rounded">AI বিশ্লেষণ</div>`;
            imagePreview.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}

// Regenerate matrix when SKU changes
document.getElementById('product-sku').addEventListener('input', generateMatrix);
</script>
@endpush
