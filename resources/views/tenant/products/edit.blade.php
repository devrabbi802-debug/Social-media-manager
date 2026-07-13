@extends('layouts.tenant')

@section('title', __('products.edit_title', ['name' => $product->name]).' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">@lang('products.edit_title', ['name' => $product->name])</h1>
                    <p class="text-gray-600">@lang('products.edit_subtitle')</p>
                </div>
                <a href="{{ route('inventory.products.index') }}" class="text-gray-600 hover:text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form action="{{ route('inventory.products.update', $product) }}" method="POST" enctype="multipart/form-data" id="productForm">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- Main Info --}}
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">@lang('products.basic_info')</h2>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">@lang('products.name') *</label>
                                <input type="text" name="name" value="{{ old('name', $product->name) }}" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">@lang('products.description')</label>
                                <textarea name="description" rows="4" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">{{ old('description', $product->description) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">@lang('products.pricing')</h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">@lang('products.price') *</label>
                                <input type="number" name="price" value="{{ old('price', $product->price) }}" step="0.01" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">@lang('products.cost_price')</label>
                                <input type="number" name="cost_price" value="{{ old('cost_price', $product->cost_price) }}" step="0.01" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">@lang('products.inventory')</h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">@lang('products.sku')</label>
                                <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">@lang('products.barcode')</label>
                                <input type="text" name="barcode" value="{{ old('barcode', $product->barcode) }}" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">@lang('products.stock_quantity')</label>
                                <input type="number" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">@lang('products.low_stock_threshold')</label>
                                <input type="number" name="low_stock_threshold" value="{{ old('low_stock_threshold', $product->low_stock_threshold) }}" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            </div>
                        </div>
                    </div>

                    {{-- Existing Images --}}
                    @if($product->images->count() > 0)
                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">@lang('products.existing_images')</h2>
                        <div class="grid grid-cols-4 gap-4">
                            @foreach($product->images as $image)
                                <div class="relative group">
                                    <img src="{{ asset('storage/' . $image->image_path) }}" class="w-full h-24 object-cover rounded-lg">
                                    @if($image->is_primary)
                                        <span class="absolute top-1 left-1 bg-purple-600 text-white text-xs px-1.5 py-0.5 rounded">@lang('products.primary')</span>
                                    @endif
                                    <label class="absolute top-1 right-1 flex items-center">
                                        <input type="checkbox" name="delete_images[]" value="{{ $image->id }}" class="w-4 h-4 text-red-600 rounded">
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">@lang('products.add_more_images')</h2>
                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-purple-500 transition">
                            <input type="file" name="images[]" id="imageInput" multiple accept="image/*" class="hidden">
                            <button type="button" onclick="document.getElementById('imageInput').click()" class="inline-flex items-center px-6 py-3 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition">
                                @lang('products.choose_images')
                            </button>
                        </div>
                        <div id="imagePreview" class="grid grid-cols-4 gap-4 mt-4"></div>
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">@lang('products.category')</h2>
                        <select name="category_id" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="">@lang('products.select_category')</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">@lang('products.brand')</h2>
                        <select name="brand_id" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="">@lang('products.select_brand')</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">@lang('products.status')</h2>
                        <label class="flex items-center space-x-3">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }} class="w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                            <span class="text-sm text-gray-700">@lang('products.active')</span>
                        </label>
                    </div>

                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <button type="submit" class="w-full bg-purple-600 text-white px-6 py-3 rounded-xl font-medium hover:bg-purple-700 transition">
                            @lang('products.update_btn')
                        </button>
                        <a href="{{ route('inventory.products.index') }}" class="block text-center text-sm text-gray-500 hover:underline mt-3">@lang('common.cancel')</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('imageInput')?.addEventListener('change', function(e) {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    Array.from(e.target.files).forEach((file) => {
        const reader = new FileReader();
        reader.onload = (ev) => {
            preview.innerHTML += `<div class="relative">
                <img src="${ev.target.result}" class="w-full h-24 object-cover rounded-lg">
                <button type="button" onclick="this.parentElement.remove()" class="absolute top-1 right-1 w-5 h-5 bg-red-500 text-white rounded-full text-xs">&times;</button>
            </div>`;
        };
        reader.readAsDataURL(file);
    });
});
</script>
@endpush
@endsection
