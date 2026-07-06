@extends('layouts.app')

@section('title', 'নতুন প্রোডাক্ট যোগ - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">নতুন প্রোডাক্ট যোগ করুন</h1>
                    <p class="text-gray-600">প্রোডাক্টের তথ্য পূরণ করুন</p>
                </div>
                <a href="{{ route('inventory.products.index') }}" class="text-gray-600 hover:text-purple-600 font-medium">← ফিরে যান</a>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form action="{{ route('inventory.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            {{-- Basic Info --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900 mb-4">মৌলিক তথ্য</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">প্রোডাক্টের নাম *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">SKU *</label>
                        <input type="text" name="sku" value="{{ old('sku') }}" required
                            class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        @error('sku') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">বারকোড</label>
                        <input type="text" name="barcode" value="{{ old('barcode') }}"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ক্যাটাগরি *</label>
                        <select name="category_id" id="category_id" required class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                            <option value="">ক্যাটাগরি নির্বাচন করুন</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
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
                            class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Pricing --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900 mb-4">মূল্য ও স্টক</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">মূল মূল্য (৳) *</label>
                        <input type="number" name="base_price" value="{{ old('base_price') }}" step="0.01" min="0" required
                            class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                        @error('base_price') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ডিসকাউন্ট মূল্য (৳)</label>
                        <input type="number" name="discount_price" value="{{ old('discount_price') }}" step="0.01" min="0"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                        @error('discount_price') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">স্টক পরিমাণ</label>
                        <input type="number" name="stock_quantity" value="{{ old('stock_quantity', 0) }}" min="0"
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
                            <option value="meter" {{ old('unit') === 'meter' ? 'selected' : '' }}>মিটার (meter)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">স্ট্যাটাস</label>
                        <select name="status" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                            <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>সক্রিয়</option>
                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>নিষ্ক্রিয়</option>
                            <option value="out_of_stock" {{ old('status') === 'out_of_stock' ? 'selected' : '' }}>স্টক শেষ</option>
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

            {{-- Dynamic Attributes --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm" id="attributes-section" style="display: none;">
                <h2 class="text-lg font-bold text-gray-900 mb-4">কাস্টম অ্যাট্রিবিউট</h2>
                <div id="attributes-container" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Dynamic attributes will be loaded here --}}
                </div>
            </div>

            {{-- Images --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900 mb-4">প্রোডাক্ট ইমেজ</h2>
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

            {{-- SEO --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900 mb-4">SEO তথ্য</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">মেটা টাইটেল</label>
                        <input type="text" name="meta_title" value="{{ old('meta_title') }}"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">মেটা বিবরণ</label>
                        <textarea name="meta_description" rows="2"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">{{ old('meta_description') }}</textarea>
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

@push('scripts')
<script>
document.getElementById('category_id').addEventListener('change', function() {
    const categoryId = this.value;
    const section = document.getElementById('attributes-section');
    const container = document.getElementById('attributes-container');

    if (!categoryId) {
        section.style.display = 'none';
        container.innerHTML = '';
        return;
    }

    fetch('{{ route("inventory.products.attributes") }}?category_id=' + categoryId)
        .then(r => r.json())
        .then(attributes => {
            if (attributes.length === 0) {
                section.style.display = 'none';
                container.innerHTML = '';
                return;
            }

            section.style.display = 'block';
            container.innerHTML = '';

            attributes.forEach(attr => {
                let input = '';
                if (attr.type === 'select') {
                    const options = (attr.options || []).map(o => `<option value="${o}">${o}</option>`).join('');
                    input = `<select name="attribute[${attr.id}]" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                        <option value="">নির্বাচন করুন</option>${options}</select>`;
                } else if (attr.type === 'boolean') {
                    input = `<select name="attribute[${attr.id}]" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                        <option value="">নির্বাচন করুন</option>
                        <option value="1">হ্যাঁ</option><option value="0">না</option></select>`;
                } else if (attr.type === 'date') {
                    input = `<input type="date" name="attribute[${attr.id}]" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">`;
                } else if (attr.type === 'number') {
                    input = `<input type="number" name="attribute[${attr.id}]" step="any" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">`;
                } else {
                    input = `<input type="text" name="attribute[${attr.id}]" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">`;
                }

                container.innerHTML += `
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">${attr.name} ${attr.is_required ? '*' : ''}</label>
                        ${input}
                    </div>`;
            });
        });
});

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
