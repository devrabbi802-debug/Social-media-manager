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
                <input type="file" name="images[]" multiple accept="image/*" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-purple-500">
                <p class="text-sm text-gray-500 mt-1">নতুন ইমেজ আপলোড করলে AI অটোমেটিক্যালি বিশ্লেষণ করবে।</p>
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
</script>
@endpush
@endsection
