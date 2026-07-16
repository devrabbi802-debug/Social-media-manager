@extends('layouts.tenant')

@section('title', __('products.create_title').' - SocialBoost AI')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">@lang('products.create_title')</h1>
                    <p class="text-gray-600">@lang('products.create_subtitle')</p>
                </div>
                <a href="{{ route('inventory.products.index') }}" class="text-gray-600 hover:text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
         x-data="productForm()"
         x-init="init()">

        {{-- Validation Errors --}}
        @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4">
            <div class="flex items-center mb-2">
                <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="font-medium text-red-800">@lang('products.validation_error')</span>
            </div>
            <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('inventory.products.store') }}" method="POST" enctype="multipart/form-data" id="productForm">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- ========== MAIN CONTENT ========== --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- LAYER 1: BASIC INFO --}}
                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">@lang('products.basic_info')</h2>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">@lang('products.name') *</label>
                                <input type="text" name="name" value="{{ old('name') }}" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                       placeholder="{{ __('products.product_name_placeholder') }}">
                                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">@lang('products.description')</label>
                                <textarea name="description" rows="4"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                          placeholder="{{ __('products.description_placeholder') }}">{{ old('description') }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- LAYER 1: PRICING --}}
                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">@lang('products.pricing')</h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">@lang('products.base_price') *</label>
                                <input type="number" name="base_price" value="{{ old('base_price') }}" step="0.01" min="0" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                       placeholder="0.00"
                                       x-model="basePrice"
                                       @input="validateDiscountPrice()">
                                @error('base_price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">@lang('products.discount_price')</label>
                                <input type="number" name="discount_price" value="{{ old('discount_price') }}" step="0.01" min="0"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                       placeholder="0.00"
                                       x-model="discountPrice"
                                       :class="discountPriceError ? 'border-red-500 ring-2 ring-red-200' : ''"
                                       @input="validateDiscountPrice()">
                                <p x-show="discountPriceError" x-cloak class="text-red-500 text-xs mt-1" x-text="discountPriceError"></p>
                                @error('discount_price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- LAYER 1: INVENTORY --}}
                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">@lang('products.inventory')</h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">@lang('products.sku') *</label>
                                <input type="text" name="sku" value="{{ old('sku') }}" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                       placeholder="{{ __('products.sku_hint') }}">
                                <p class="text-xs text-gray-400 mt-1">@lang('products.sku_hint')</p>
                                @error('sku') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">@lang('products.barcode')</label>
                                <input type="text" name="barcode" value="{{ old('barcode') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                @error('barcode') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Unit: hidden if Digital --}}
                            <div x-show="!isDigital" x-transition>
                                <label class="block text-sm font-medium text-gray-700 mb-1">@lang('products.unit')</label>
                                <select name="unit" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                    <option value="pcs" {{ old('unit') == 'pcs' ? 'selected' : '' }}>pcs</option>
                                    <option value="kg" {{ old('unit') == 'kg' ? 'selected' : '' }}>kg</option>
                                    <option value="ltr" {{ old('unit') == 'ltr' ? 'selected' : '' }}>ltr</option>
                                    <option value="mtr" {{ old('unit') == 'mtr' ? 'selected' : '' }}>mtr</option>
                                    <option value="pair" {{ old('unit') == 'pair' ? 'selected' : '' }}>pair</option>
                                    <option value="set" {{ old('unit') == 'set' ? 'selected' : '' }}>set</option>
                                    <option value="box" {{ old('unit') == 'box' ? 'selected' : '' }}>box</option>
                                </select>
                                @error('unit') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <template x-if="isDigital">
                                <input type="hidden" name="unit" value="">
                            </template>

                            {{-- Stock: hidden if variants exist --}}
                            <div x-show="!hasVariants" x-transition>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    @lang('products.stock_quantity')
                                    <span x-show="!isDigital" class="text-red-500">*</span>
                                </label>
                                <input type="number" name="stock_quantity" value="{{ old('stock_quantity', 0) }}" min="0"
                                       :required="!isDigital"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                @error('stock_quantity') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Weight --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">@lang('products.weight_kg')</label>
                                <input type="number" name="weight_kg" value="{{ old('weight_kg') }}" step="0.01" min="0"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                       placeholder="0.00">
                                @error('weight_kg') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- LAYER 1B: VARIANT SYSTEM --}}
                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-bold text-gray-900">@lang('products.variants')</h2>
                            <button type="button" @click="showVariantModal = true"
                                    class="inline-flex items-center px-4 py-2 bg-purple-100 text-purple-700 rounded-xl text-sm font-medium hover:bg-purple-200 transition">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                @lang('products.add_option')
                            </button>
                        </div>

                        {{-- Selected variant options --}}
                        <div x-show="selectedOptions.length > 0" class="mb-4">
                            <div class="flex flex-wrap gap-2">
                                <template x-for="(opt, idx) in selectedOptions" :key="idx">
                                    <div class="inline-flex items-center bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-sm">
                                        <span x-text="opt.name"></span>
                                        <button type="button" @click="removeOption(idx)" class="ml-2 text-purple-500 hover:text-purple-700">&times;</button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Variant option value inputs --}}
                        <template x-for="(opt, idx) in selectedOptions" :key="'values-'+idx">
                            <div class="mb-4 p-4 bg-gray-50 rounded-xl">
                                <label class="block text-sm font-medium text-gray-700 mb-2" x-text="opt.name + ' values (comma separated)'"></label>
                                <input type="text" x-model="opt.valuesInput"
                                       @input="generateMatrix()"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                       :placeholder="'e.g. ' + opt.defaultValues.join(', ')">
                                <p class="text-xs text-gray-400 mt-1" x-show="opt.defaultValues.length > 0">
                                    @lang('products.default_values'): <span x-text="opt.defaultValues.join(', ')"></span>
                                </p>
                            </div>
                        </template>

                        {{-- Combination cap warning --}}
                        <div x-show="combinationCount > 50" x-cloak
                             class="mb-4 p-3 rounded-xl text-sm"
                             :class="combinationCount > 100 ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-yellow-50 text-yellow-700 border border-yellow-200'">
                            <span x-show="combinationCount <= 100">⚠️ <span x-text="combinationCount"></span> @lang('products.variant_soft_warning')</span>
                            <span x-show="combinationCount > 100">🚫 <span x-text="combinationCount"></span> @lang('products.variant_hard_block')</span>
                        </div>

                        {{-- Variant matrix table --}}
                        <div x-show="matrixVariants.length > 0" x-cloak class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <template x-for="opt in selectedOptions" :key="'th-'+opt.name">
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase" x-text="opt.name"></th>
                                        </template>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">@lang('products.price')</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">@lang('products.stock')</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">@lang('products.barcode')</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <template x-for="(variant, idx) in matrixVariants" :key="'row-'+idx">
                                        <tr>
                                            <template x-for="opt in selectedOptions" :key="'td-'+opt.name+idx">
                                                <td class="px-4 py-3 text-sm text-gray-900" x-text="variant.combo[opt.name]"></td>
                                            </template>
                                            <td class="px-4 py-3">
                                                <input type="text" :name="'variants['+idx+'][sku]'" x-model="variant.sku"
                                                       class="w-32 px-2 py-1 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500">
                                            </td>
                                            <td class="px-4 py-3">
                                                <input type="number" :name="'variants['+idx+'][price]'" x-model="variant.price" step="0.01" min="0"
                                                       class="w-24 px-2 py-1 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500">
                                            </td>
                                            <td class="px-4 py-3">
                                                <input type="number" :name="'variants['+idx+'][stock_quantity]'" x-model="variant.stock" min="0"
                                                       :required="!isDigital"
                                                       class="w-20 px-2 py-1 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500">
                                            </td>
                                            <td class="px-4 py-3">
                                                <input type="text" :name="'variants['+idx+'][barcode]'" x-model="variant.barcode"
                                                       class="w-28 px-2 py-1 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500">
                                            </td>
                                            {{-- Hidden input for attributes --}}
                                            <template x-for="(val, attrName) in variant.combo" :key="'attr-'+attrName+idx">
                                                <input type="hidden" :name="'variants['+idx+'][attributes]['+attrName+']'" :value="val">
                                            </template>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                            <p class="text-xs text-gray-400 mt-2">@lang('products.sku_auto_hint')</p>
                        </div>

                        <div x-show="selectedOptions.length === 0" class="text-center py-8 text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                            <p>@lang('products.no_options')</p>
                            <p class="text-sm mt-1">@lang('products.single_product_hint')</p>
                        </div>
                    </div>

                    {{-- LAYER 2: CATEGORY EXTRA FIELDS --}}
                    @if(!empty($extraFields))
                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <h2 class="text-lg font-bold text-gray-900 mb-1">@lang('products.category_fields')</h2>
                        <p class="text-sm text-gray-500 mb-4">{{ $businessCategory->name }} @lang('products.extra_fields_hint')</p>

                        <div class="space-y-4">
                            @foreach($extraFields as $field)
                            <div>
                                @if($field['type'] === 'textarea')
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ $field['label'] ?? $field['name'] }}
                                        @if($field['required'] ?? false) <span class="text-red-500">*</span> @endif
                                    </label>
                                    <textarea name="extra[{{ $field['name'] }}]" rows="3"
                                              class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                              placeholder="{{ $field['placeholder'] ?? '' }}">{{ old("extra.{$field['name']}") }}</textarea>

                                @elseif($field['type'] === 'text')
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ $field['label'] ?? $field['name'] }}
                                        @if($field['required'] ?? false) <span class="text-red-500">*</span> @endif
                                    </label>
                                    <input type="text" name="extra[{{ $field['name'] }}]" value="{{ old("extra.{$field['name']}", $field['default'] ?? '') }}"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                           placeholder="{{ $field['placeholder'] ?? '' }}">

                                @elseif($field['type'] === 'number')
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ $field['label'] ?? $field['name'] }}
                                        @if($field['required'] ?? false) <span class="text-red-500">*</span> @endif
                                    </label>
                                    <input type="number" name="extra[{{ $field['name'] }}]" value="{{ old("extra.{$field['name']}", $field['default'] ?? '') }}"
                                           step="any" min="0"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                           placeholder="{{ $field['placeholder'] ?? '' }}">

                                @elseif($field['type'] === 'boolean')
                                    <label class="flex items-center space-x-3 cursor-pointer">
                                        <input type="hidden" name="extra[{{ $field['name'] }}]" value="0">
                                        <input type="checkbox" name="extra[{{ $field['name'] }}]" value="1"
                                               {{ old("extra.{$field['name']}") ? 'checked' : '' }}
                                               class="w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                                        <span class="text-sm text-gray-700">{{ $field['label'] ?? $field['name'] }}</span>
                                    </label>

                                @elseif($field['type'] === 'select')
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ $field['label'] ?? $field['name'] }}
                                        @if($field['required'] ?? false) <span class="text-red-500">*</span> @endif
                                    </label>
                                    <select name="extra[{{ $field['name'] }}]"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                        <option value="">@lang('products.select_placeholder')</option>
                                        @foreach($field['options'] ?? [] as $opt)
                                            <option value="{{ $opt }}" {{ (old("extra.{$field['name']}") == $opt || ($field['default'] ?? '') == $opt) ? 'selected' : '' }}>{{ $opt }}</option>
                                        @endforeach
                                    </select>
                                @endif

                                @error("extra.{$field['name']}") <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                {{-- ========== SIDEBAR ========== --}}
                <div class="space-y-6">
                    {{-- Category --}}
                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">@lang('products.category')</h2>
                        <select name="category_id"
                                x-model="selectedCategoryId"
                                @change="onCategoryChange()"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="">@lang('products.select_category')</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                                @if($category->children->count())
                                    @foreach($category->children as $child)
                                        <option value="{{ $child->id }}" {{ old('category_id') == $child->id ? 'selected' : '' }}>
                                            &nbsp;&nbsp;{{ $child->name }}
                                        </option>
                                    @endforeach
                                @endif
                            @endforeach
                        </select>
                        @error('category_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Brand --}}
                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">@lang('products.brand')</h2>
                        <select name="brand_id" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="">@lang('products.select_brand')</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                        @error('brand_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Status & Featured --}}
                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">@lang('products.status')</h2>
                        <div class="space-y-3">
                            <div>
                                <select name="status" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                    <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>@lang('products.active')</option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>@lang('products.inactive')</option>
                                    <option value="out_of_stock" {{ old('status') == 'out_of_stock' ? 'selected' : '' }}>@lang('products.out_of_stock')</option>
                                </select>
                            </div>
                            <label class="flex items-center space-x-3">
                                <input type="hidden" name="is_featured" value="0">
                                <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}
                                       class="w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                                <span class="text-sm text-gray-700">@lang('products.featured')</span>
                            </label>
                        </div>
                    </div>

                    {{-- Images --}}
                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">@lang('products.images')</h2>
                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-purple-500 transition">
                            <input type="file" name="images[]" id="imageInput" multiple accept="image/*" class="hidden">
                            <button type="button" onclick="document.getElementById('imageInput').click()"
                                    class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition text-sm">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                @lang('products.choose_images')
                            </button>
                            <p class="text-xs text-gray-500 mt-2">@lang('products.image_formats')</p>
                        </div>
                        <div id="imagePreview" class="grid grid-cols-3 gap-3 mt-4"></div>
                        @error('images') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- SEO --}}
                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">@lang('products.seo_info')</h2>
                        <div class="space-y-3">
                            <input type="text" name="meta_title" value="{{ old('meta_title') }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm"
                                   placeholder="{{ __('products.meta_title_placeholder') }}">
                            <textarea name="meta_description" rows="2"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm"
                                      placeholder="{{ __('products.meta_desc_placeholder') }}">{{ old('meta_description') }}</textarea>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <button type="submit"
                                :disabled="combinationCount > 100"
                                class="w-full bg-purple-600 text-white px-6 py-3 rounded-xl font-medium hover:bg-purple-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                            @lang('products.save_product')
                        </button>
                        <a href="{{ route('inventory.products.index') }}" class="block text-center text-sm text-gray-500 hover:underline mt-3">@lang('common.cancel')</a>
                    </div>
                </div>
            </div>
        </form>

        {{-- Variant Options Modal --}}
        <div x-show="showVariantModal" x-cloak x-transition class="fixed inset-0 z-50 overflow-y-auto" style="display:none;">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black/50" @click="showVariantModal = false"></div>
                <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">@lang('products.select_variant_options')</h3>
                    <div class="space-y-2">
                        @foreach($variantOptions as $opt)
                        <label class="flex items-center space-x-3 p-3 rounded-xl hover:bg-gray-50 cursor-pointer"
                               :class="isOptionSelected('{{ $opt->name }}') ? 'bg-purple-50 border border-purple-200' : ''">
                            <input type="checkbox"
                                   :checked="isOptionSelected('{{ $opt->name }}')"
                                   @change="toggleOption('{{ $opt->name }}', @js($opt->options ?? []))"
                                   class="w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                            <span class="text-sm font-medium text-gray-700">{{ $opt->name }}</span>
                        </label>
                        @endforeach
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button type="button" @click="showVariantModal = false"
                                class="px-6 py-2 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition">
                            @lang('common.done')
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function productForm() {
    return {
        // Form data
        selectedCategoryId: '{{ old("category_id", $tenantCategory?->id ?? "") }}',
        basePrice: '{{ old("base_price") }}',
        discountPrice: '{{ old("discount_price") }}',
        discountPriceError: '',

        // Category info
        isDigital: {{ $isDigital ? 'true' : 'false' }},
        tenantCategoryId: {{ $tenantCategory?->id ?? 'null' }},

        // Variant system
        selectedOptions: [],
        showVariantModal: false,
        hasVariants: false,
        matrixVariants: [],
        combinationCount: 0,

        init() {
            this.generateMatrix();
        },

        validateDiscountPrice() {
            const base = parseFloat(this.basePrice) || 0;
            const discount = parseFloat(this.discountPrice) || 0;
            if (discount > 0 && discount >= base) {
                this.discountPriceError = '@lang("products.discount_must_be_less")';
            } else {
                this.discountPriceError = '';
            }
        },

        onCategoryChange() {
            // Show/hide extra fields based on category
            // (handled by x-show in the template)
        },

        isOptionSelected(name) {
            return this.selectedOptions.some(o => o.name === name);
        },

        toggleOption(name, defaultValues) {
            const idx = this.selectedOptions.findIndex(o => o.name === name);
            if (idx >= 0) {
                this.selectedOptions.splice(idx, 1);
            } else {
                this.selectedOptions.push({
                    name: name,
                    defaultValues: defaultValues || [],
                    valuesInput: (defaultValues || []).join(', ')
                });
            }
            this.generateMatrix();
        },

        removeOption(idx) {
            this.selectedOptions.splice(idx, 1);
            this.generateMatrix();
        },

        generateMatrix() {
            const optionValues = this.selectedOptions.map(opt => {
                const raw = opt.valuesInput || '';
                const vals = raw.split(',').map(v => v.trim()).filter(v => v);
                if (vals.length === 0 && opt.defaultValues.length > 0) {
                    return opt.defaultValues;
                }
                return vals;
            }).filter(v => v.length > 0);

            if (optionValues.length === 0) {
                this.matrixVariants = [];
                this.combinationCount = 0;
                this.hasVariants = false;
                return;
            }

            // Cartesian product
            let combos = [[]];
            for (const vals of optionValues) {
                const newCombos = [];
                for (const combo of combos) {
                    for (const val of vals) {
                        newCombos.push([...combo, val]);
                    }
                }
                combos = newCombos;
            }

            this.combinationCount = combos.length;

            // Generate variant rows with auto-SKU
            const baseSku = document.querySelector('input[name="sku"]')?.value || 'PRD';
            this.matrixVariants = combos.map((combo, idx) => {
                const comboObj = {};
                this.selectedOptions.forEach((opt, i) => {
                    comboObj[opt.name] = combo[i];
                });

                // Auto-SKU: BASE-VAL1-VAL2
                const skuParts = [baseSku, ...combo.map(v => v.replace(/\s+/g, '').substring(0, 5).toUpperCase())];
                const autoSku = skuParts.join('-');

                return {
                    combo: comboObj,
                    sku: autoSku,
                    price: '',
                    stock: '',
                    barcode: ''
                };
            });

            this.hasVariants = this.matrixVariants.length > 0;
        }
    };
}

// Image preview
document.getElementById('imageInput')?.addEventListener('change', function(e) {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    Array.from(e.target.files).forEach((file, i) => {
        const reader = new FileReader();
        reader.onload = (ev) => {
            preview.innerHTML += `<div class="relative">
                <img src="${ev.target.result}" class="w-full h-20 object-cover rounded-lg">
                <button type="button" onclick="this.parentElement.remove()" class="absolute top-1 right-1 w-5 h-5 bg-red-500 text-white rounded-full text-xs flex items-center justify-center">&times;</button>
            </div>`;
        };
        reader.readAsDataURL(file);
    });
});
</script>
@endpush
@endsection
